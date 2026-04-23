<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Person extends Model
{
    protected $fillable = [
        'personable_id',
        'personable_type',
        'email',
        'phone'
    ];
    public function personable(): MorphTo
    {
        return $this->morphTo();
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
