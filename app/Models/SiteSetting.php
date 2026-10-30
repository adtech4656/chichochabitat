<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'locale', 'value', 'type', 'label', 'hint'];

    protected static function booted(): void
    {
        $flush = fn () => Settings::flush();

        static::saved($flush);
        static::deleted($flush);
    }

    /** Secrets are never stored in plaintext, and are language-independent. */
    public static function putSecret(string $key, string $label, ?string $value): void
    {
        self::updateOrCreate(
            ['key' => $key, 'locale' => 'en'],
            [
                'group' => 'integrations',
                'label' => $label,
                'type' => 'secret',
                'value' => filled($value) ? Crypt::encryptString($value) : null,
            ],
        );
    }

    public static function getSecret(string $key): ?string
    {
        $raw = self::query()->where('key', $key)->value('value');

        if (blank($raw)) {
            return null;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    /** True when a secret exists, without ever decrypting it in a form. */
    public static function hasSecret(string $key): bool
    {
        return filled(self::query()->where('key', $key)->value('value'));
    }
}
