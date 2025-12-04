<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'facility_id',
        'booking_reference',
        'booking_type',
        'start_time',
        'end_time',
        'booking_date',
        'end_date',
        'number_of_people',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
