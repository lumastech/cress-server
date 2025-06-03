<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'log_name',
        'description',
        'subject_id',
        'subject_type',
        'causer_id',
        'causer_type',
        'properties',
        'event',
        'batch_uuid',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'properties' => 'collection',
    ];

    public function subject()
    {
        return $this->morphTo();
    }

    public function causer()
    {
        return $this->morphTo();
    }

    public function scopeInLog($query, ...$logNames)
    {
        return $query->whereIn('log_name', $logNames);
    }

    public function scopeCausedBy($query, $causer)
    {
        return $query->where('causer_type', $causer->getMorphClass())
                    ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_type', $subject->getMorphClass())
                    ->where('subject_id', $subject->getKey());
    }
}
