<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = ['user_id', 'loan_id', 'transaction_id', 'amount', 'paid_at', 'notes'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime'];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
