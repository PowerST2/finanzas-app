<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'wallet_id', 'destination_wallet_id', 'category_id', 'loan_id', 'type', 'amount', 'destination_amount', 'exchange_rate', 'original_amount', 'original_currency', 'date', 'month', 'description', 'status', 'metadata'];

    protected $casts = ['amount' => 'decimal:2', 'destination_amount' => 'decimal:2', 'exchange_rate' => 'decimal:6', 'original_amount' => 'decimal:2', 'date' => 'datetime', 'metadata' => 'array'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function destinationWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'destination_wallet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class);
    }
}
