<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featured = Property::query()
            ->published()
            ->with('agent')
            ->latest('published_at')
            ->take(6)
            ->get();

        $published = fn (): Builder => Property::query()->published();

        return Inertia::render('Home', [
            'properties' => $featured->map->toCardArray()->values(),

            // Real inventory counts per category — never invented numbers.
            'categories' => [
                'buy' => $published()->where('intent', 'sale')->count(),
                'rent' => $published()->where('intent', 'rent')->count(),
                'land' => $published()->where('type', 'Land')->count(),
                'commercial' => $published()->where('type', 'Shop')->count(),
                'hospitality' => $published()->where('type', 'Hotel')->count(),
            ],

            // Latest approved listings double as the market feed on the homepage.
            'market' => $featured->take(3)->map(fn (Property $property): array => [
                'id' => $property->id,
                'region' => $property->city,
                'published' => $property->published_at?->toIso8601String(),
                'title' => $property->title,
                'type' => $property->type,
                'intent_label' => __($property->intent === 'rent' ? 'prop.month' : 'prop.sale'),
                'area' => $property->area,
                'price' => $property->price,
                'href' => '/properties/'.$property->slug,
            ])->values(),

            // Every string below is admin-editable (Admin → Site settings).
            'content' => [
                'eyebrow' => Settings::get('hero.eyebrow'),
                'title_line1' => Settings::get('hero.title_line1'),
                'title_line2' => Settings::get('hero.title_line2'),
                'subtitle' => Settings::get('hero.subtitle'),
                'tagline' => Settings::get('site.tagline'),
                'stats' => [
                    // Live counts; only the response-time claim is editorial.
                    'listings' => (string) $published()->count(),
                    'agents' => (string) User::query()
                        ->whereHas('roles', fn ($q) => $q->where('name', 'agent')->where('guard_name', 'web'))
                        ->count(),
                    'approval' => $this->approvalRate(),
                    'response' => Settings::get('stats.response'),
                ],
            ],
        ]);
    }

    /** Share of decided listings that were approved, to one decimal. */
    protected function approvalRate(): string
    {
        $approved = Property::query()->where('status', Property::STATUS_APPROVED)->count();
        $rejected = Property::query()->where('status', Property::STATUS_REJECTED)->count();
        $decided = $approved + $rejected;

        if ($decided === 0) {
            return '—';
        }

        return number_format($approved / $decided * 100, 1, '.', '');
    }
}
