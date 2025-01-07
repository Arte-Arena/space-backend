<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'start_at',
        'end_at',
        'is_active',
    ];

    public function states()
    {
        return $this->belongsToMany(State::class, 'calendar_event_location');
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'calendar_event_location');
    }
}
