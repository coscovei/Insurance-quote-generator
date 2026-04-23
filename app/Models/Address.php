<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'person_id', 'country', 'county', 'city', 'city_code',
        'street', 'house_number', 'building', 'staircase',
        'apartment', 'floor', 'postcode'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
