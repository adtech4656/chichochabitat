<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocumentAccess extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'property_id',
        'agent_id',
        'created_by',
        'last_reissued_by',
        'document_access_code',
        'status',
        'issued_at',
        'expires_at',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public static function issueFor(Property $property, User $agent, User $issuer): self
    {
        $code = (string) random_int(100000, 999999);

        $access = self::query()
            ->where('property_id', $property->id)
            ->where('agent_id', $agent->id)
            ->firstOrNew();

        $access->forceFill([
            'property_id' => $property->id,
            'agent_id' => $agent->id,
            'created_by' => $access->exists ? $access->created_by ?? $issuer->id : $issuer->id,
            'last_reissued_by' => $issuer->id,
            'document_access_code' => $code,
            'status' => self::STATUS_ACTIVE,
            'issued_at' => now(),
            'expires_at' => now()->addDays(14),
            'activated_at' => null,
        ])->save();

        return $access->fresh();
    }

    public function canUnlockFor(User $user, string $code): bool
    {
        if ((int) $this->agent_id !== (int) $user->id) {
            return false;
        }

        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->expires_at !== null && now()->greaterThan($this->expires_at)) {
            return false;
        }

        return hash_equals((string) $this->document_access_code, (string) $code);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
