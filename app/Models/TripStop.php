<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripStop extends Model
{
    protected $fillable = [
        'trip_id',
        'sequence',
        'stop_type',
        'destination',
        'latitude',
        'longitude',
        'arrival_time',
        'departure_time',
        'arrival_latitude',
        'arrival_longitude',
        'status',
        'added_by',
        'added_at',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'added_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}