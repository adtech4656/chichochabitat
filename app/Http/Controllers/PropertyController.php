<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SavedProperty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function index(): Response
    {
        $properties = Property::query()
            ->published()
            ->with('agent')
            ->latest('published_at')
            ->get()
            ->map->toCardArray()
            ->values();

        return Inertia::render('Search', [
            'properties' => $properties,
            'filters' => [
                'cities' => Property::query()->published()->distinct()->orderBy('city')->pluck('city')->all(),
                'types' => Property::query()->published()->distinct()->orderBy('type')->pluck('type')->all(),
                'subTypes' => Property::query()->published()->whereNotNull('sub_type')->distinct()->orderBy('sub_type')->pluck('sub_type')->all(),
                'amenities' => collect(Property::query()->published()->pluck('tags'))
                    ->flatten()
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->all(),
                'budgets' => [
                    ['label' => 'Under 25M', 'min' => 0, 'max' => 25_000_000],
                    ['label' => '25M – 60M', 'min' => 25_000_000, 'max' => 60_000_000],
                    ['label' => '60M – 120M', 'min' => 60_000_000, 'max' => 120_000_000],
                    ['label' => '120M+', 'min' => 120_000_000, 'max' => null],
                ],
            ],
            'stats' => [
                'listings' => Property::query()->published()->count(),
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $property = Property::query()
            ->published()
            ->with('agent')
            ->where('slug', $slug)
            ->firstOrFail();

        // Count a view without touching updated_at.
        $property->incrementQuietly('views');

        $similar = Property::query()
            ->published()
            ->with('agent')
            ->where('id', '!=', $property->id)
            ->where('type', $property->type)
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map->toCardArray()
            ->values();

        return Inertia::render('PropertyShow', [
            'property' => $property->toCardArray(),
            'similar' => $similar,
        ]);
    }

    public function toggleSave(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401, 'Please sign in to save properties.');

        $existing = SavedProperty::query()
            ->where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            SavedProperty::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
            ]);
            $saved = true;
        }

        Cache::forget("user_{$user->id}_saved_{$property->id}");

        return response()->json([
            'saved' => $saved,
            'property_id' => $property->id,
        ]);
    }
}
