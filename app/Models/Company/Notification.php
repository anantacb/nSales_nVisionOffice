<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notification extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notification';

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($notification) {
            $notification->schedule()->delete();
            $notification->targets()->delete();
        });
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(NotificationSchedule::class, "NotificationId", "Id");
    }

    public function targets(): HasMany
    {
        return $this->hasMany(NotificationTarget::class, "NotificationId", "Id");
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(NotificationTopic::class, "Notificationtopicnotification",
            "NotificationId", "NotificationTopicId", "Id", "Id")
            ->withTimestamps();
    }
}
