<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringRule extends Model
{
    protected $fillable = ['user_id', 'wallet_id', 'category_id', 'type', 'frequency', 'amount', 'description', 'next_at', 'last_generated_at', 'is_active'];

    protected $casts = ['amount' => 'decimal:2', 'next_at' => 'datetime', 'last_generated_at' => 'datetime', 'is_active' => 'boolean'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
