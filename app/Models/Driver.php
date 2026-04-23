<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'insurance_request_id', 'last_name', 'first_name',
        'tax_id', 'id_number', 'mobile_number', 'driving_license_issue_date'
    ];

    public function insuranceRequest()
    {
        return $this->belongsTo(InsuranceRequest::class);
    }
}
