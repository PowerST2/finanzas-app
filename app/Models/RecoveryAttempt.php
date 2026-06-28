<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryAttempt extends Model
{
    protected $fillable = ['email', 'ip', 'successful'];

    protected $casts = ['successful' => 'boolean'];
}
