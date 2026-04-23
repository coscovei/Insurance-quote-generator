<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'insurance_request_id',
        'insurer_name',
        'price',
        'external_id',
        'raw_data',
        'status',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    protected $appends = [
        'pdf_url',
    ];

    public function getPdfUrlAttribute(): ?string
    {
        return data_get($this->raw_data, 'data.offers.0.pid');
    }

    public function insuranceRequest()
    {
        return $this->belongsTo(InsuranceRequest::class);
    }
}
