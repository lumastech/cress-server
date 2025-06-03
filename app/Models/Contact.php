<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'phone',                // Crucial for SMS notifications
        'email',               // For email notifications or if user has CRESS app
        'relationship',       // Optional: e.g., Spouse, Parent, Friend
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // No specific casts needed for these fields by default
    ];

    /**
     * Get the user who owns this emergency contact.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

