<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'business_name', 'tax_id', 'registry_number', 'caen_code'
    ];

    public function person()
    {
        return $this->morphOne(Person::class, 'personable');
    }
}
