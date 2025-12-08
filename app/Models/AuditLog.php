<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;
    protected $fillable = [
    'admin_id',
    'action',
    'model',
    'model_id',
    'old_value',
    'new_value',
    'ip'
];

}
