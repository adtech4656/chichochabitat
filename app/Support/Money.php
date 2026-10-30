<?php

namespace App\Support;

use App\Models\Currency;
use App\Models\ExchangeRateLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Currency conversion with live rates.
 *
 * All prices are stored once, in the base currency (XOF). Conversion happens
 * only at display time, so a rate change never rewrites a listing and the
 * amount a buyer is charged always matches the stored price at that moment.
 */
class Money
{
    public const BASE = 'XOF';

    protected const CACHE_KEY = 'cch.currencies';

    /** @return array<string, array{code:string,name:string,symbol:string,decimals:int,rate:float}> */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function (): array {
            return Currency::query()
                ->where('is_active', true)
                ->orderByDesc('is_base')
                ->get()
                ->mapWithKeys(fn (Currency $c) => [
                    $c->code => [
                        'code' => $c->code,
                        'name' => $c->name,
                        'symbol' => $c->symbol,
                        'decimals' => $c->decimals,
                        'rate' => (float) $c->rate_to_base,
                    ],
                ])
                ->all();
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** Convert an amount stored in base currency into the target currency. */
    public static function convert(int|float $amountInBase, string $to = self::BASE): float
    {
        if (strtoupper($to) === self::BASE) {
            return (float) $amountInBase;
        }

        $rate = self::all()[strtoupper($to)]['rate'] ?? 1.0;

        return $amountInBase * $rate;
    }

    /** Reverse a displayed amount back to the stored base currency. */
    public static function toBase(int|float $amount, string $from = self::BASE): float
    {
        if (strtoupper($from) === self::BASE) {
            return (float) $amount;
        }

        $rate = self::all()[strtoupper($from)]['rate'] ?? 1.0;

        return $rate > 0 ? $amount / $rate : (float) $amount;
    }

    /**
     * Pull live rates from a public FX provider and store them.
     *
     * The provider quotes against the base, so we persist a single multiplier
     * per currency. Rates are logged so the back office can see their history.
     */
    public static function refreshFromApi(): array
    {
        $base = self::BASE;
        $currencies = Currency::query()->where('is_active', true)->where('is_base', false)->get();

        if ($currencies->isEmpty()) {
            return [];
        }

        try {
            $response = Http::timeout(12)->get("https://open.er-api.com/v6/latest/{$base}");

            if (! $response->successful()) {
                throw new \RuntimeException('Rate provider returned '.$response->status());
            }

            $rates = $response->json('rates') ?? [];
            $updated = [];

            foreach ($currencies as $currency) {
                $rate = $rates[$currency->code] ?? null;

                if (! is_numeric($rate) || $rate <= 0) {
                    continue;
                }

                $currency->forceFill([
                    'rate_to_base' => $rate,
                    'rate_updated_at' => now(),
                ])->save();

                ExchangeRateLog::create([
                    'code' => $currency->code,
                    'rate_to_base' => $rate,
                    'source' => 'api',
                    'recorded_at' => now(),
                ]);

                $updated[$currency->code] = $rate;
            }

            self::flush();

            return $updated;
        } catch (\Throwable $exception) {
            Log::warning('Exchange rate refresh failed: '.$exception->getMessage());

            return [];
        }
    }

    /** Manual override, used by administrators. */
    public static function setRate(string $code, float $rate): void
    {
        $currency = Currency::query()->where('code', strtoupper($code))->firstOrFail();

        $currency->forceFill([
            'rate_to_base' => $rate,
            'rate_updated_at' => now(),
        ])->save();

        ExchangeRateLog::create([
            'code' => $currency->code,
            'rate_to_base' => $rate,
            'source' => 'manual',
            'recorded_at' => now(),
        ]);

        self::flush();
    }
}
