<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceRequest extends Model {
    protected $fillable = ['vehicle_id', 'policyholder_id', 'owner_id', 'start_date', 'term_time', 'target_provider'];
    public function policyholder() {
        return $this->belongsTo(Person::class, 'policyholder_id');
    }
    public function owner() {
        return $this->belongsTo(Person::class, 'owner_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function drivers() {
        return $this->hasMany(Driver::class);
    }
}
