<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Cocody Villa with Garden',
            'Abidjan City Apartment',
            'Plateau Commercial Space',
            'Yamoussoukro Family House',
            'Land in Grand-Bassam',
            'Boutique Hotel Suite',
        ]);

        $type = fake()->randomElement(['House', 'Land', 'Shop', 'Hotel']);
        $intent = fake()->randomElement(['sale', 'rent']);
        $city = fake()->randomElement(['Abidjan', 'Yamoussoukro', 'Bouaké', 'San Pedro', 'Grand-Bassam']);
        $district = fake()->randomElement(['Cocody', 'Plateau', 'Marcory', 'Le Plateau', 'Anoumabo', 'Akwaba']);

        return [
            'agent_id' => User::factory(),
            'country_code' => 'CI',
            'tenant_id' => null,
            'reference' => 'CCH-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'description' => fake()->paragraph(3),
            'intent' => $intent,
            'type' => $type,
            'district' => $district,
            'city' => $city,
            'price' => fake()->numberBetween(2500000, 250000000),
            'currency' => 'XOF',
            'platform_fee' => fake()->numberBetween(50000, 3500000),
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 5),
            'area' => fake()->numberBetween(80, 700),
            'latitude' => fake()->latitude(4.9, 5.5),
            'longitude' => fake()->longitude(-4.2, -3.8),
            'status' => Property::STATUS_PENDING,
            'required_shots' => 8,
            'captured_shots' => 8,
            'documents_count' => 3,
            'gps_delta_m' => 12,
            'agent_kyc_status' => 'approved',
            'review_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'published_at' => now(),
            'views' => 0,
            'cover_image' => 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=80',
            'gallery' => [
                'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
            ],
            'tags' => ['verified', 'featured'],
        ];
    }
}
