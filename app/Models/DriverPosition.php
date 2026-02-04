<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverPosition extends Model
{
    protected $fillable = [
        'driver_id',
        'occurrence_id',
        'latitude',
        'longitude',
        'accuracy',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'driver_id' => 'integer',
        'occurrence_id' => 'integer',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function occurrence()
    {
        return $this->belongsTo(Occurrences::class, 'occurrence_id');
    }

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForOccurrence($query, ?int $occurrenceId)
    {
        if ($occurrenceId === null) {
            return $query->whereNull('occurrence_id');
        }

        return $query->where('occurrence_id', $occurrenceId);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
