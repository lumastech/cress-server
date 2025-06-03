<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

     protected $fillable = [
        'name',
        'ref_id',
        'file_path',
        'type',
        'status'
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    // public function getUrlAttribute()
    // {
    //     return Storage::disk($this->disk)->url($this->file_path);
    // }

    // public function getHumanReadableSizeAttribute()
    // {
    //     return format_bytes($this->size);
    // }

}
