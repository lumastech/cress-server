<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traites\LogsActivity;

class Image extends Model
{
    use HasFactory;
    use LogsActivity;


    protected $fillable = [
        'name',
        'ref_id',
        'image_path',
        'type',
        'status'
    ];

    public function parent() {
        return $this->belongsTo(Center::class, 'ref_id');
    }
}
