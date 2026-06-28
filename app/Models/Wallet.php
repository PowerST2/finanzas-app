<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'currency', 'exchange_rate_to_pen', 'opening_balance', 'current_balance_cache', 'is_active'];

    protected $casts = ['opening_balance' => 'decimal:2', 'current_balance_cache' => 'decimal:2', 'exchange_rate_to_pen' => 'decimal:4', 'is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
