<?php

namespace App\Support;

/**
 * Single source of realistic showcase inventory for the design build.
 *
 * Everything the UI renders comes from here so the screens tell one coherent
 * story. When the Property engine lands, swap these methods for Eloquent
 * queries with the same return shape — no React component needs to change.
 */
class DemoInventory
{
    public static function stats(): array
    {
        return [
            'listings' => '12.4k',
            'agents' => '1,860',
            'approval' => '98.7',
            'response' => '4 min',
        ];
    }

    public static function agents(): array
    {
        return [
            'aya' => [
                'id' => 1,
                'name' => 'Aya Koffi',
                'agency' => 'Koffi & Co. Immobilier',
                'initials' => 'AK',
                'rating' => 4.9,
                'deals' => 128,
                'verified' => true,
                'since' => '2021',
                'response' => 'Under 10 min',
                'phone' => '+225 07 00 00 01',
            ],
            'jeanmarc' => [
                'id' => 2,
                'name' => 'Jean-Marc Diallo',
                'agency' => 'Atlas Habitat',
                'initials' => 'JD',
                'rating' => 4.8,
                'deals' => 94,
                'verified' => true,
                'since' => '2022',
                'response' => 'Under 30 min',
                'phone' => '+225 07 00 00 02',
            ],
            'fatou' => [
                'id' => 3,
                'name' => 'Fatou Bamba',
                'agency' => 'Ivoire Estates',
                'initials' => 'FB',
                'rating' => 4.7,
                'deals' => 61,
                'verified' => true,
                'since' => '2023',
                'response' => 'Same day',
                'phone' => '+225 07 00 00 03',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function properties(): array
    {
        return [
            self::property(
                id: 1,
                slug: 'mansard-view-residence',
                title: 'Mansard View Residence',
                district: 'Cocody',
                city: 'Abidjan',
                price: 54_000_000,
                intent: 'sale',
                type: 'House',
                bedrooms: 4,
                bathrooms: 3,
                area: 280,
                cover: 'photo-1600585154340-be6161a56a0c',
                gallery: ['photo-1600585154340-be6161a56a0c', 'photo-1600607687939-ce8a6c25118c', 'photo-1600566753190-17f0baa2a6c3', 'photo-1600210492486-724fe5c67fb0'],
                agent: 'aya',
                tags: ['Pool', 'Garden', 'Parking', 'Security', 'Solar'],
                description: 'A calm, light-filled family home set back from the road in Cocody. Double-height living space opens onto a shaded terrace and a filtered pool, with a separate service wing and covered parking for two cars. Every photograph in this listing was captured on site with GPS and timestamp verification.',
                lat: 5.3599,
                lng: -3.9866,
                published: '2026-09-28',
                views: 1284,
            ),
            self::property(
                id: 2,
                slug: 'sunset-creek-villa',
                title: 'Sunset Creek Villa',
                district: 'Riviera',
                city: 'Abidjan',
                price: 38_500_000,
                intent: 'sale',
                type: 'House',
                bedrooms: 3,
                bathrooms: 2,
                area: 220,
                cover: 'photo-1600596542815-ffad4c1539a9',
                gallery: ['photo-1600596542815-ffad4c1539a9', 'photo-1600607687920-4e2a09cf159d', 'photo-1600585154526-990dced4db0d'],
                agent: 'jeanmarc',
                tags: ['Garden', 'Parking', 'Security', 'Borehole'],
                description: 'Quiet villa with a mature garden and room to extend. The kitchen was renovated in 2024 and the roof was replaced the same year. Title documents have been reviewed by our legal team.',
                lat: 5.3673,
                lng: -3.9551,
                published: '2026-10-02',
                views: 941,
            ),
            self::property(
                id: 3,
                slug: 'atelier-loft-plateau',
                title: 'Atelier Loft — Plateau',
                district: 'Plateau',
                city: 'Abidjan',
                price: 1_250_000,
                intent: 'rent',
                type: 'House',
                bedrooms: 2,
                bathrooms: 2,
                area: 118,
                cover: 'photo-1512917774080-9991f1c4c750',
                gallery: ['photo-1512917774080-9991f1c4c750', 'photo-1600210492486-724fe5c67fb0'],
                agent: 'fatou',
                tags: ['Furnished', 'Air conditioning', 'Elevator', 'Security'],
                description: 'Converted loft minutes from the Plateau business district. Furnished, air-conditioned, and available immediately on a twelve-month minimum term.',
                lat: 5.3242,
                lng: -4.0161,
                published: '2026-10-08',
                views: 612,
            ),
            self::property(
                id: 4,
                slug: 'cocody-garden-estate',
                title: 'Cocody Garden Estate',
                district: 'Angré',
                city: 'Abidjan',
                price: 76_000_000,
                intent: 'sale',
                type: 'House',
                bedrooms: 5,
                bathrooms: 4,
                area: 410,
                cover: 'photo-1613490493576-7fde63acd811',
                gallery: ['photo-1613490493576-7fde63acd811', 'photo-1600607687939-ce8a6c25118c'],
                agent: 'aya',
                tags: ['Pool', 'Garden', 'Parking', 'Security', 'Solar'],
                description: 'A five-bedroom estate on a 900 m² titled plot, with staff quarters, a pool, and a solar array that covers daytime load.',
                lat: 5.4028,
                lng: -3.9702,
                published: '2026-09-19',
                views: 2104,
            ),
            self::property(
                id: 5,
                slug: 'palm-ridge-plot',
                title: 'Palm Ridge Titled Plot',
                district: 'Bingerville',
                city: 'Abidjan',
                price: 18_900_000,
                intent: 'sale',
                type: 'Land',
                bedrooms: 0,
                bathrooms: 0,
                area: 600,
                cover: 'photo-1500382017468-9049fed747ef',
                gallery: ['photo-1500382017468-9049fed747ef'],
                agent: 'jeanmarc',
                tags: ['Titled', 'Corner plot', 'Road access'],
                description: 'Corner plot with an ADU certificate on file and clear road access. Ideal for a duplex or a small residence.',
                lat: 5.3556,
                lng: -3.8836,
                published: '2026-10-11',
                views: 388,
            ),
            self::property(
                id: 6,
                slug: 'marina-court-commercial',
                title: 'Marina Court Commercial',
                district: 'Marcory',
                city: 'Abidjan',
                price: 2_400_000,
                intent: 'rent',
                type: 'Shop',
                bedrooms: 0,
                bathrooms: 1,
                area: 95,
                cover: 'photo-1497366754035-f200968a6e72',
                gallery: ['photo-1497366754035-f200968a6e72'],
                agent: 'fatou',
                tags: ['Street frontage', 'Parking', 'Security', 'Air conditioning'],
                description: 'Ground-floor retail unit with nine metres of street frontage in a high-footfall stretch of Marcory.',
                lat: 5.3021,
                lng: -3.9905,
                published: '2026-10-14',
                views: 274,
            ),
            self::property(
                id: 7,
                slug: 'lagune-bleue-hotel',
                title: 'Lagune Bleue Boutique Hotel',
                district: 'Grand-Bassam',
                city: 'Grand-Bassam',
                price: 285_000_000,
                intent: 'sale',
                type: 'Hotel',
                bedrooms: 18,
                bathrooms: 18,
                area: 1250,
                cover: 'photo-1582719478250-c89cae4dc85b',
                gallery: ['photo-1582719478250-c89cae4dc85b', 'photo-1445019980597-93fa8acb246c'],
                agent: 'aya',
                tags: ['Pool', 'Restaurant', 'Beachfront', 'Security'],
                description: 'Eighteen-key boutique property on the lagoon side of Grand-Bassam, trading since 2019 with an established bookings base.',
                lat: 5.2058,
                lng: -3.7386,
                published: '2026-09-08',
                views: 3320,
            ),
            self::property(
                id: 8,
                slug: 'akwaba-family-home',
                title: 'Akwaba Family Home',
                district: 'Yopougon',
                city: 'Abidjan',
                price: 24_500_000,
                intent: 'sale',
                type: 'House',
                bedrooms: 3,
                bathrooms: 2,
                area: 165,
                cover: 'photo-1568605114967-8130f3a36994',
                gallery: ['photo-1568605114967-8130f3a36994', 'photo-1600585154526-990dced4db0d'],
                agent: 'jeanmarc',
                tags: ['Garden', 'Parking', 'Borehole'],
                description: 'Practical three-bedroom home on a quiet residential street, within walking distance of schools and a market.',
                lat: 5.3364,
                lng: -4.0856,
                published: '2026-10-05',
                views: 733,
            ),
            self::property(
                id: 9,
                slug: 'riviera-terrace-duplex',
                title: 'Riviera Terrace Duplex',
                district: 'Riviera 4',
                city: 'Abidjan',
                price: 1_800_000,
                intent: 'rent',
                type: 'House',
                bedrooms: 4,
                bathrooms: 3,
                area: 240,
                cover: 'photo-1570129477492-45c003edd2be',
                gallery: ['photo-1570129477492-45c003edd2be', 'photo-1600566753190-17f0baa2a6c3'],
                agent: 'fatou',
                tags: ['Garden', 'Parking', 'Security', 'Air conditioning'],
                description: 'Duplex with a private garden in a gated Riviera 4 compound. Available unfurnished from November.',
                lat: 5.3711,
                lng: -3.9403,
                published: '2026-10-16',
                views: 189,
            ),
        ];
    }

    public static function find(string $slug): ?array
    {
        foreach (self::properties() as $property) {
            if ($property['slug'] === $slug) {
                return $property;
            }
        }

        return null;
    }

    public static function similar(string $slug, int $limit = 3): array
    {
        $current = self::find($slug);

        if (! $current) {
            return [];
        }

        $sameType = array_values(array_filter(
            self::properties(),
            fn (array $property) => $property['slug'] !== $slug && $property['type'] === $current['type'],
        ));

        $others = array_values(array_filter(
            self::properties(),
            fn (array $property) => $property['slug'] !== $slug && $property['type'] !== $current['type'],
        ));

        return array_slice([...$sameType, ...$others], 0, $limit);
    }

    public static function offers(): array
    {
        return [
            [
                'id' => 101,
                'property' => self::find('mansard-view-residence'),
                'amount' => 51_500_000,
                'status' => 'pending',
                'placed' => '2 days ago',
                'agent' => self::agents()['aya'],
            ],
            [
                'id' => 102,
                'property' => self::find('sunset-creek-villa'),
                'amount' => 37_000_000,
                'status' => 'pending',
                'placed' => '5 hours ago',
                'agent' => self::agents()['jeanmarc'],
            ],
            [
                'id' => 103,
                'property' => self::find('atelier-loft-plateau'),
                'amount' => 1_200_000,
                'status' => 'accepted',
                'placed' => 'Accepted yesterday',
                'agent' => self::agents()['fatou'],
                'deal' => [
                    'reference' => 'CCH-2026-0482',
                    'closed' => '16 Oct 2026, 14:32',
                    'terms' => '12-month lease · 2 months deposit',
                ],
            ],
        ];
    }

    public static function chats(): array
    {
        return [
            [
                'id' => 201,
                'property' => self::find('mansard-view-residence'),
                'agent' => self::agents()['aya'],
                'unread' => 2,
                'status' => 'open',
                'messages' => [
                    ['from' => 'agent', 'text' => 'Good morning — the residence is still available. Would you like to see it this week?', 'at' => 'Yesterday, 09:14'],
                    ['from' => 'me', 'text' => 'Yes please. Is Saturday morning possible?', 'at' => 'Yesterday, 09:21'],
                    ['from' => 'agent', 'text' => 'Saturday at 10:00 works. I will bring the title documents.', 'at' => 'Yesterday, 09:24'],
                ],
            ],
            [
                'id' => 202,
                'property' => self::find('atelier-loft-plateau'),
                'agent' => self::agents()['fatou'],
                'unread' => 0,
                'status' => 'closed',
                'closed_reason' => 'Offer accepted',
                'deal' => [
                    'reference' => 'CCH-2026-0482',
                    'closed' => '16 Oct 2026, 14:32',
                    'terms' => '12-month lease · 2 months deposit',
                ],
                'messages' => [],
            ],
        ];
    }

    public static function saved(): array
    {
        return [
            self::find('cocody-garden-estate'),
            self::find('lagune-bleue-hotel'),
            self::find('riviera-terrace-duplex'),
        ];
    }

    /**
     * Queue for the backend verification screen.
     */
    public static function verificationQueue(): array
    {
        $items = [];

        foreach (self::properties() as $index => $property) {
            $items[] = [
                'property' => $property,
                'submitted' => $property['published'],
                'status' => $index === 0 ? 'in_review' : ($index < 4 ? 'pending' : 'approved'),
                'gps_delta' => [12, 34, 8, 61, 19, 4, 27, 44, 9][$index] ?? 10,
                'required_shots' => 8,
                'captured_shots' => $index === 5 ? 6 : 8,
                'documents' => $index === 5 ? 2 : 3,
                'agent_kyc' => $index === 5 ? 'pending' : 'approved',
            ];
        }

        return $items;
    }

    /**
     * Normalise a raw record into the shape every React surface expects.
     *
     * @param  array<int, string>  $gallery
     * @param  array<int, string>  $tags
     */
    private static function property(
        int $id,
        string $slug,
        string $title,
        string $district,
        string $city,
        int $price,
        string $intent,
        string $type,
        int $bedrooms,
        int $bathrooms,
        int $area,
        string $cover,
        array $gallery,
        string $agent,
        array $tags,
        string $description,
        float $lat,
        float $lng,
        string $published,
        int $views,
    ): array {
        $intentLabel = $intent === 'rent' ? 'Per month' : 'For sale';

        return [
            'id' => $id,
            'slug' => $slug,
            'title' => $title,
            'location' => "{$district}, {$city}",
            'district' => $district,
            'city' => $city,
            'price' => $price,
            'currency' => 'XOF',
            'intent' => $intent,
            'intent_label' => $intentLabel,
            'type' => $type,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'area' => $area,
            'image' => self::img($cover, 1400),
            'gallery' => array_map(fn (string $photo) => self::img($photo, 1600), $gallery),
            'agent' => self::agents()[$agent],
            'tags' => $tags,
            'description' => $description,
            'lat' => $lat,
            'lng' => $lng,
            'published' => $published,
            'views' => $views,
            'href' => "/properties/{$slug}",
            'verified' => [
                'agent' => true,
                'location' => true,
                'photos' => true,
                'video' => true,
                'documents' => true,
                'reviewer' => 'Technical review · K. Mensah',
                'reviewed_at' => $published,
            ],
        ];
    }

    private static function img(string $photoId, int $width = 1200): string
    {
        return "https://images.unsplash.com/{$photoId}?auto=format&fit=crop&w={$width}&q=80";
    }
}
