<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateLog extends Model
{
    protected $fillable = ['code', 'rate_to_base', 'source', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'rate_to_base' => 'float',
            'recorded_at' => 'datetime',
        ];
    }
}
