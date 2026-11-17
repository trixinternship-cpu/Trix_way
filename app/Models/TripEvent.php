<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripEvent extends Model
{
    protected $fillable = [
        'trip_id',
        'actor_id',
        'event_type',
        'description',
        'metadata',
        'event_time',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'event_time' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
