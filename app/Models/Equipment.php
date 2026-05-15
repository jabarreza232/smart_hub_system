<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';
    protected $fillable = ['name', 'type', 'stock', 'status', 'sku', 'condition_notes', 'location'];
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
