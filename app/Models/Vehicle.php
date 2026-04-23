<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model {
    protected $fillable = [
        'license_plate', 'registration_type', 'vin', 'vehicle_type', 'brand', 'model',
        'year_of_construction', 'engine_displacement', 'engine_power', 'total_weight',
        'seats', 'fuel_type', 'first_registration', 'usage_type', 'civ_number',
        'current_mileage', 'has_mobility_modifications', 'is_leased', 'is_new'
    ];
}
