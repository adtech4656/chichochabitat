<?php

namespace App\Console\Commands;

use App\Support\Money;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'rates:refresh';

    protected $description = 'Pull live exchange rates and update the currency table';

    public function handle(): int
    {
        $updated = Money::refreshFromApi();

        if ($updated === []) {
            $this->warn('No rates were updated. Existing rates were kept.');

            return self::FAILURE;
        }

        foreach ($updated as $code => $rate) {
            $this->line(sprintf('  %s  %s', $code, $rate));
        }

        $this->info('Exchange rates updated.');

        return self::SUCCESS;
    }
}
