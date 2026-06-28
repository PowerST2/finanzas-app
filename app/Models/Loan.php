<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = ['user_id', 'wallet_id', 'kind', 'name', 'lender_name', 'principal_amount', 'current_balance', 'interest_rate', 'received_at', 'due_date', 'status', 'notes'];

    protected $casts = ['principal_amount' => 'decimal:2', 'current_balance' => 'decimal:2', 'interest_rate' => 'decimal:2', 'received_at' => 'datetime', 'due_date' => 'date'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
