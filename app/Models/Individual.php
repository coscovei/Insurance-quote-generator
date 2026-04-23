<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Individual extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'tax_id', 'birthdate',
        'gender', 'id_type', 'id_series', 'id_number',
        'is_retired', 'has_disability', 'driving_license_issue_date'
    ];

    public function person()
    {
        return $this->morphOne(Person::class, 'personable');
    }
}
