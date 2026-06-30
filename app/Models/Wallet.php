<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'currency', 'exchange_rate_to_pen', 'opening_balance', 'current_balance_cache', 'is_active', 'credit_cycle_started_at', 'credit_cycle_start_day', 'credit_cycle_close_day', 'credit_payment_due_day', 'credit_reset_day'];

    protected $casts = ['opening_balance' => 'decimal:2', 'current_balance_cache' => 'decimal:2', 'exchange_rate_to_pen' => 'decimal:4', 'is_active' => 'boolean', 'credit_cycle_started_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
