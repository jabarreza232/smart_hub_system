<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['user_id', 'equipment_id', 'start_time', 'end_time', 'status'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
