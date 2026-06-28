<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyOption extends Model
{
    protected $fillable = ['code', 'name', 'exchange_rate_to_pen', 'is_active'];

    protected $casts = ['exchange_rate_to_pen' => 'decimal:4', 'is_active' => 'boolean'];
}
