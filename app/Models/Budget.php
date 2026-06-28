<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = ['user_id', 'month', 'total_limit', 'saving_goal'];

    protected $casts = ['total_limit' => 'decimal:2', 'saving_goal' => 'decimal:2'];

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
