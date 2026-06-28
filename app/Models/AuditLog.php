<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'auditable_type', 'auditable_id', 'action', 'old_values', 'new_values', 'notes'];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
}
