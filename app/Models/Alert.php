<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'severity', 'triggered_at', 'read_at', 'metadata'];

    protected $casts = ['triggered_at' => 'datetime', 'read_at' => 'datetime', 'metadata' => 'array'];
}
