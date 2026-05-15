<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'equipment_id',
        'booking_code',
        'start_time',
        'end_time',
        'actual_check_in',  // WAJIB ADA
        'actual_check_out', // WAJIB ADA
        'status',
        'admin_notes'
    ];
    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s',
        'end_time' => 'datetime:Y-m-d H:i:s',
        'actual_check_in' => 'datetime:Y-m-d H:i:s',
        'actual_check_out' => 'datetime:Y-m-d H:i:s',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
