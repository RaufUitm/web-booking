<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = ['date', 'start_time', 'end_time', 'is_available'];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean'
    ];

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }
}
