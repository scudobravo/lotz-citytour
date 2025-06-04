<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointOfInterest extends Model
{
    protected $table = 'points_of_interest';

    protected $fillable = [
        'order_number',
        'name',
        'latitude',
        'longitude',
        'description',
        'image_path',
        'audio_path',
        'project_id'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}