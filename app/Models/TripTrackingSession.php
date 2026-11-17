<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripTrackingSession extends Model
{
    protected $fillable = [
        'trip_id',
        'status',
        'started_at',
        'stopped_at',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'last_recorded_at',
        'last_received_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
            'last_recorded_at' => 'datetime',
            'last_received_at' => 'datetime',

            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy' => 'decimal:2',
            'speed' => 'decimal:2',
            'heading' => 'decimal:2',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
