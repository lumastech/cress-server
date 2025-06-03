<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;
    protected $fillable=[
        'ip',
        'device_os',
        'device_os_version',
        'device_type',
        'browser',
        'browser_version',
    ];
}
