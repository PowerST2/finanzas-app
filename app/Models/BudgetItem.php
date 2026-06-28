<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $fillable = ['budget_id', 'category_id', 'limit_amount', 'alert_80', 'alert_90', 'alert_100'];

    protected $casts = ['limit_amount' => 'decimal:2', 'alert_80' => 'boolean', 'alert_90' => 'boolean', 'alert_100' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
