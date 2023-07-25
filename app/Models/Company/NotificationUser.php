<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NotificationUser extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notificationuser';

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->topics()->detach();
        });
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(NotificationTopic::class, "Notificationtopicsubscriber",
            "NotificationUserId", "NotificationTopicId", "Id", "Id")
            ->withPivot('Channel')
            ->withTimestamps();
    }
}
