<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Read-through cache for admin-editable site content.
 *
 * Every visitor-facing number or sentence comes from here so the back office
 * is the single source of truth. Values are stored per language; a missing
 * translation falls back to the application fallback locale.
 */
class Settings
{
    public const LOCALES = ['en', 'fr'];

    protected const CACHE_PREFIX = 'cch.settings';

    /**
     * Raw values for one locale only.
     *
     * @return array<string, string|null>
     */
    public static function forLocale(?string $locale = null): array
    {
        $locale = self::normalize($locale);

        return Cache::rememberForever(
            self::CACHE_PREFIX.'.'.$locale,
            fn (): array => SiteSetting::query()
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->all()
        );
    }

    /**
     * Resolved map: the requested language overrides the fallback language,
     * and a blank translation never shadows a real value.
     *
     * @return array<string, string|null>
     */
    public static function all(?string $locale = null): array
    {
        $locale = self::normalize($locale);
        $fallback = self::normalize(config('app.fallback_locale'));

        $base = self::forLocale($fallback);

        if ($locale === $fallback) {
            return $base;
        }

        $translated = array_filter(
            self::forLocale($locale),
            fn ($value): bool => filled($value),
        );

        return array_merge($base, $translated);
    }

    public static function get(string $key, ?string $default = null, ?string $locale = null): ?string
    {
        $value = self::all($locale)[$key] ?? null;

        return filled($value) ? $value : $default;
    }

    public static function int(string $key, int $default = 0, ?string $locale = null): int
    {
        $value = self::get($key, null, $locale);

        return is_numeric($value) ? (int) $value : $default;
    }

    /** Only configured languages may reach the cache, so a crafted locale cannot poison it. */
    public static function normalize(?string $locale): string
    {
        $locale = strtolower(trim((string) ($locale ?: app()->getLocale())));

        return in_array($locale, self::LOCALES, true) ? $locale : 'en';
    }

    public static function flush(): void
    {
        foreach (self::LOCALES as $locale) {
            Cache::forget(self::CACHE_PREFIX.'.'.$locale);
        }
    }
}
