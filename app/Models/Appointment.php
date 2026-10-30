<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'conversation_id',
        'property_id',
        'customer_id',
        'agent_id',
        'scheduled_at',
        'verification_code',
        'location',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public static function generateVerificationCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    public function verifyCode(string $code): bool
    {
        return hash_equals((string) $this->verification_code, (string) $code);
    }

    public function markVerified(): self
    {
        $this->forceFill([
            'status' => 'verified',
            'verified_at' => now(),
        ])->save();

        return $this->fresh();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
