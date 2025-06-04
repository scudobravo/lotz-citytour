<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monument extends Model
{
    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'image'
    ];
} 