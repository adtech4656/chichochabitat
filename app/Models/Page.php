<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'locale',
        'title',
        'group',
        'body',
        'meta_description',
        'is_published',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * Resolve a page in the visitor's language.
     *
     * Falls back to the application fallback locale so a page that has not
     * been translated yet still renders instead of 404ing.
     */
    public static function forLocale(string $slug, ?string $locale = null): ?self
    {
        $locale = Settings::normalize($locale);

        $page = static::query()->published()
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->first();

        if ($page) {
            return $page;
        }

        $fallback = Settings::normalize(config('app.fallback_locale'));

        return static::query()->published()
            ->where('slug', $slug)
            ->where('locale', $fallback)
            ->first();
    }

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            $page->slug = Str::slug($page->slug ?: $page->title);
            $page->locale = Settings::normalize($page->locale);

            // Sanitise at the boundary. Even a compromised or malicious staff
            // account can then only persist markup that is safe to render on
            // the shared origin.
            if ($page->isDirty('body')) {
                $page->body = HtmlSanitizer::clean($page->body);
            }

            if ($page->isDirty('meta_description') && $page->meta_description !== null) {
                $page->meta_description = Str::limit(
                    trim(strip_tags($page->meta_description)),
                    320,
                    ''
                );
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
