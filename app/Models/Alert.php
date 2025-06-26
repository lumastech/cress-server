<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traites\LogsActivity;


class Alert extends Model
{
    use HasFactory;
    use LogsActivity;


    protected $fillable = [
        'user_id',
        'name',
        'status',
        'message',
        'initiated_at',
        'lat',
        'lng',
        'accuracy'
    ];


    /**
     * Get the user who triggered this alert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
