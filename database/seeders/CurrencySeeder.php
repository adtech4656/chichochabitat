<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;


class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['XOF', 'West African CFA franc', 'F CFA', 0, 1.0, true],
            ['USD', 'US Dollar', '$', 2, 0.0017, false],
            ['EUR', 'Euro', '€', 2, 0.0015, false],
            ['NGN', 'Nigerian Naira', '₦', 0, 2.6, false],
            ['GHS', 'Ghanaian Cedi', '₵', 2, 0.021, false],
        ];

        foreach ($currencies as [$code, $name, $symbol, $decimals, $rate, $isBase]) {
            Currency::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'symbol' => $symbol,
                    'decimals' => $decimals,
                    'rate_to_base' => $rate,
                    'is_active' => true,
                    'is_base' => $isBase,
                    'rate_updated_at' => now(),
                ],
            );
        }

        $this->command?->info('Seeded '.count($currencies).' currencies.');
    }
}
 