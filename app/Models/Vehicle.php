<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    public const CATEGORIES = [
        'SUV',
        'Saloon',
        'Pickup',
        'Van',
        'Hatchback',
        'Coupe',
        'Other',
    ];

    protected $fillable = [
        'number_plate',
        'chassis_number',
        'engine_capacity',
        'make',
        'year_of_make',
        'category',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year_of_make' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }

    public function displayName(): string
    {
        return "{$this->make} ({$this->number_plate})";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
