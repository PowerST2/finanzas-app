<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendingDebt extends Model
{
    protected $fillable = ['user_id', 'name', 'total_amount', 'current_balance', 'currency', 'due_date', 'status', 'notes'];

    protected $casts = ['total_amount' => 'decimal:2', 'current_balance' => 'decimal:2', 'due_date' => 'date'];

    public function payments(): HasMany
    {
        return $this->hasMany(PendingDebtPayment::class);
    }
}
