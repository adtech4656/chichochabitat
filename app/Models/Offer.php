<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = [
        'property_id', 'user_id', 'amount', 'discount_percent',
        'status', 'message', 'decided_by', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'discount_percent' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
