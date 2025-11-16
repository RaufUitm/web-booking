<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'facility_id',
        'start_time',
        'end_time',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
