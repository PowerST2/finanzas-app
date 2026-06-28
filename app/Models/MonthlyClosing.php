<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyClosing extends Model
{
    protected $fillable = ['user_id', 'month', 'income', 'expense', 'balance'];

    protected $casts = ['income' => 'decimal:2', 'expense' => 'decimal:2', 'balance' => 'decimal:2'];
}
