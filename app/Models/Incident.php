<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable  = [
        'user_id',
        'name',
        'type',
        'area',
        'details',
        'status',
        'lat',
        'lng'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
