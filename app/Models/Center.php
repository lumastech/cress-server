<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'type',
        'lat',
        'lng',
        'status',
        'address',
        'description',
        'is_verified',
    ];

     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_verified' => 'boolean',
        // 'operating_hours' => 'array', // If storing as JSON
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images() {
        return $this->hasMany(Image::class, 'ref_id')->where('type', 'center');
    }
    public function files() {
        return $this->hasMany(Image::class, 'ref_id')->where('type', 'center');
    }
}
