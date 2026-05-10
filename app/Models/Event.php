<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'event_date', 'location', 'total_seats', 'available_seats', 'organizer_id'];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
