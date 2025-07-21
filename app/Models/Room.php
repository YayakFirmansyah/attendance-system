<?php
// app/Models/Room.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_code',
        'room_name',
        'building',
        'floor',
        'capacity',
        'type',
        'facilities',
        'status'
    ];

    // Relationships
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->room_code . ' - ' . $this->room_name;
    }
}