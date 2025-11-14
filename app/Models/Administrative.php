<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Administrative extends Model
{
    use HasApiTokens;

    protected $table = 'administrative';

    protected $fillable = [
        'name',
        'user_name',
        'password',
        'role', // 1 أو 2
        'id_agency'
    ];

    protected $hidden = [
        'password'
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'id_agency');
    }
}
