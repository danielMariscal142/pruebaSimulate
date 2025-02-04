<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;
    protected $table = 'directions';
    protected $fillable = [
        'status',
        'user_id',
        'latitude',
        'longitude',
        'place_id',
        'label'
    ];
}
