<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code', 'name', 'symbol', 'decimals', 'rate_to_base',
        'is_active', 'is_base', 'rate_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'rate_to_base' => 'float',
            'is_active' => 'boolean',
            'is_base' => 'boolean',
            'rate_updated_at' => 'datetime',
        ];
    }
}
