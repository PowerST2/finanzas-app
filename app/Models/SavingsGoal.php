<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsGoal extends Model
{
    protected $fillable = ['user_id', 'wallet_id', 'name', 'target_amount', 'current_amount', 'target_date', 'status'];

    protected $casts = ['target_amount' => 'decimal:2', 'current_amount' => 'decimal:2', 'target_date' => 'date'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
