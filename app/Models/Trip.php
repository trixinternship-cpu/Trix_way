<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Trip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_code',
        'driver_id',
        'vehicle_id',
        'created_by',
        'purpose',
        'origin',
        'origin_latitude',
        'origin_longitude',
        'planned_start',
        'start_time',
        'end_time',
        'end_latitude',
        'end_longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'planned_start' => 'datetime',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'origin_latitude' => 'decimal:7',
            'origin_longitude' => 'decimal:7',
            'end_latitude' => 'decimal:7',
            'end_longitude' => 'decimal:7',
        ];
    }

   public function driver(): BelongsTo
{
    return $this->belongsTo(User::class, 'driver_id')->withTrashed();
}
    public function vehicle(): BelongsTo
{
    return $this->belongsTo(Vehicle::class, 'vehicle_id')
        ->withTrashed();
}

    public function stops(): HasMany
    {
        return $this->hasMany(TripStop::class)->orderBy('sequence');
    }

    public function trackingSession(): HasOne
    {
        return $this->hasOne(TripTrackingSession::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(TripLocation::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TripEvent::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(TripNotification::class);
    }
}
