<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleBooking extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'title',
        'destination',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeOverlapping(
        Builder $query,
        int $vehicleId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeId = null
    ): Builder {
        return $query
            ->where('vehicle_id', $vehicleId)
            ->confirmed()
            ->when($excludeId, fn (Builder $q) => $q->where('id', '!=', $excludeId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);
    }

    public static function hasConflict(
        int $vehicleId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeId = null
    ): bool {
        return static::overlapping($vehicleId, $startsAt, $endsAt, $excludeId)->exists();
    }
}
