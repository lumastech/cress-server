<?php

namespace App\Traites;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        static::updated(function (Model $model) {
            $model->logActivity('updated');
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });
    }

    public function logActivity(string $event, array $properties = [])
    {
        ActivityLog::create([
            'subject_id' => $this->getKey(),
            'subject_type' => $this->getMorphClass(),
            'causer_id' => auth()->id(),
            'causer_type' => auth()->user() ? get_class(auth()->user()) : null,
            'description' => $this->getActivityDescription($event),
            'event' => $event,
            'properties' => array_merge($properties, $this->getDirty()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function getActivityDescription(string $event): string
    {
        $modelName = class_basename($this);
        return "{$event} {$modelName}";
    }
}
