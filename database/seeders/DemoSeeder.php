<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeds the back office with a realistic verification queue.
 *
 * Idempotent: re-running updates existing records rather than duplicating them.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Never overwrite the administrator's password here — that account is
        // owned by AccessControlSeeder. We only need a reviewer to attribute
        // decisions to.
        $admin = User::query()->where('role', 'admin')->first()
            ?? User::create([
                'name' => 'System Administrator',
                'email' => 'admin@chichochabitat.ci',
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
            ]);
        $admin->forceFill(['role' => 'admin'])->save();
        $admin->syncRoles(Role::findOrCreate('admin', 'web'));

        $agents = collect([
            ['name' => 'Aya Koffi', 'email' => 'aya@koffi.ci', 'phone' => '+225 07 00 00 01'],
            ['name' => 'Jean-Marc Diallo', 'email' => 'jeanmarc@atlas.ci', 'phone' => '+225 07 00 00 02'],
            ['name' => 'Fatou Bamba', 'email' => 'fatou@ivoire.ci', 'phone' => '+225 07 00 00 03'],
        ])->mapWithKeys(function (array $agent): array {
            $user = User::updateOrCreate(
                ['email' => $agent['email']],
                $agent + [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
            $user->forceFill(['role' => 'agent'])->save();
            $user->syncRoles(Role::findOrCreate('agent', 'web'));

            return [$agent['email'] => $user];
        });

        $rows = [
            ['Mansard View Residence', 'Cocody', 'Abidjan', 54_000_000, 'sale', 'House', 4, 3, 280, 'in_review', 8, 8, 3, 12, 'approved', 'aya@koffi.ci', 5.3599, -3.9866],
            ['Sunset Creek Villa', 'Riviera', 'Abidjan', 38_500_000, 'sale', 'House', 3, 2, 220, 'pending', 8, 8, 3, 34, 'approved', 'jeanmarc@atlas.ci', 5.3673, -3.9551],
            ['Atelier Loft — Plateau', 'Plateau', 'Abidjan', 1_250_000, 'rent', 'House', 2, 2, 118, 'pending', 8, 8, 3, 8, 'approved', 'fatou@ivoire.ci', 5.3242, -4.0161],
            ['Cocody Garden Estate', 'Angré', 'Abidjan', 76_000_000, 'sale', 'House', 5, 4, 410, 'pending', 8, 8, 3, 61, 'approved', 'aya@koffi.ci', 5.4028, -3.9702],
            ['Palm Ridge Titled Plot', 'Bingerville', 'Abidjan', 18_900_000, 'sale', 'Land', 0, 0, 600, 'approved', 8, 8, 3, 19, 'approved', 'jeanmarc@atlas.ci', 5.3556, -3.8836],
            ['Marina Court Commercial', 'Marcory', 'Abidjan', 2_400_000, 'rent', 'Shop', 0, 1, 95, 'approved', 8, 6, 2, 4, 'pending', 'fatou@ivoire.ci', 5.3021, -3.9905],
            ['Lagune Bleue Boutique Hotel', 'Grand-Bassam', 'Grand-Bassam', 285_000_000, 'sale', 'Hotel', 18, 18, 1250, 'approved', 8, 8, 3, 27, 'approved', 'aya@koffi.ci', 5.2058, -3.7386],
            ['Akwaba Family Home', 'Yopougon', 'Abidjan', 24_500_000, 'sale', 'House', 3, 2, 165, 'approved', 8, 8, 3, 44, 'approved', 'jeanmarc@atlas.ci', 5.3364, -4.0856],
            ['Riviera Terrace Duplex', 'Riviera 4', 'Abidjan', 1_800_000, 'rent', 'House', 4, 3, 240, 'needs_info', 8, 8, 3, 9, 'approved', 'fatou@ivoire.ci', 5.3711, -3.9403],
        ];

        $images = [
            'mansard-view-residence' => ['photo-1600585154340-be6161a56a0c', 'photo-1600607687939-ce8a6c25118c'],
            'sunset-creek-villa' => ['photo-1600596542815-ffad4c1539a9', 'photo-1600607687920-4e2a09cf159d'],
            'atelier-loft-plateau' => ['photo-1512917774080-9991f1c4c750', 'photo-1600210492486-724fe5c67fb0'],
            'cocody-garden-estate' => ['photo-1613490493576-7fde63acd811', 'photo-1600607687939-ce8a6c25118c'],
            'palm-ridge-titled-plot' => ['photo-1500382017468-9049fed747ef'],
            'marina-court-commercial' => ['photo-1497366754035-f200968a6e72'],
            'lagune-bleue-boutique-hotel' => ['photo-1582719478250-c89cae4dc85b', 'photo-1445019980597-93fa8acb246c'],
            'akwaba-family-home' => ['photo-1568605114967-8130f3a36994'],
            'riviera-terrace-duplex' => ['photo-1570129477492-45c003edd2be'],
        ];

        $tags = [
            'House' => ['Garden', 'Parking', 'Security', 'Solar'],
            'Land' => ['Titled', 'Road access', 'Corner plot'],
            'Shop' => ['Street frontage', 'Parking', 'Air conditioning'],
            'Hotel' => ['Pool', 'Restaurant', 'Beachfront', 'Security'],
        ];

        $subTypes = [
            0 => 'villa', 1 => 'villa', 2 => 'duplex', 3 => 'villa',
            4 => 'plot', 5 => 'retail', 6 => 'hotel', 7 => 'bungalow', 8 => 'duplex',
        ];

        foreach ($rows as $index => [$title, $district, $city, $price, $intent, $type, $beds, $baths, $area, $status, $required, $captured, $documents, $delta, $kyc, $agentEmail, $lat, $lng]) {
            $agent = $agents[$agentEmail];
            $slug = Str::slug($title);
            $photoIds = $images[$slug] ?? ['photo-1600585154340-be6161a56a0c'];

            $url = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1400&q=80";

            $property = Property::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'agent_id' => $agent->id,
                    'description' => "{$title} in {$district}, {$city}. Every photograph in this listing was captured on site with GPS and timestamp verification.",
                    'cover_image' => $url($photoIds[0]),
                    'gallery' => array_map($url, $photoIds),
                    'tags' => $tags[$type] ?? [],
                    'intent' => $intent,
                    'type' => $type,
                    'sub_type' => $subTypes[$index] ?? null,
                    'district' => $district,
                    'city' => $city,
                    'price' => $price,
                    // Platform fee is added on top and hidden from the customer.
                    'platform_fee' => $intent === 'rent' ? 50_000 : (int) round($price * 0.025),
                    'bedrooms' => $beds,
                    'bathrooms' => $baths,
                    'area' => $area,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status' => $status,
                    'required_shots' => $required,
                    'captured_shots' => $captured,
                    'documents_count' => $documents,
                    'gps_delta_m' => $delta,
                    'agent_kyc_status' => $kyc,
                    'views' => random_int(180, 3400),
                ],
            );

            if ($status === Property::STATUS_APPROVED && $property->published_at === null) {
                $property->forceFill([
                    'published_at' => now()->subDays(random_int(1, 20)),
                    'reviewed_at' => now()->subDays(random_int(2, 25)),
                    'reviewed_by' => $admin->id,
                ])->save();
            }
        }

        $this->command?->info('Seeded '.Property::count().' properties and '.User::count().' users.');
    }
}
