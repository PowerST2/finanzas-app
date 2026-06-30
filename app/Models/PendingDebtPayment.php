<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingDebtPayment extends Model
{
    protected $fillable = ['user_id', 'pending_debt_id', 'transaction_id', 'amount', 'paid_at', 'notes'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime'];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(PendingDebt::class, 'pending_debt_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
