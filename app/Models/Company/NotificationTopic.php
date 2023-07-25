<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NotificationTopic extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notificationtopic';

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($topic) {
            static::generateIdentifier($topic);
        });

        static::deleting(function ($topic) {
            $topic->conditions()->delete();
            $topic->users()->detach();
        });
    }

    private static function generateIdentifier($topic): void
    {
        $count = 0;
        $identifier = Str::slug($topic->Topic, "-");
        while (NotificationTopic::where("Identifier", $identifier)->first()) {
            $count += 1;
            $identifier = Str::slug($topic->Topic, "-") . '-' . $count;
        }
        $topic->Identifier = $identifier;
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(NotificationTopicCondition::class, "NotificationTopicId", "Id");
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(NotificationUser::class, "Notificationtopicsubscriber",
            "NotificationTopicId", "NotificationUserId", "Id", "Id")
            ->withPivot('Channel')
            ->withTimestamps();
    }

    public function usersByChannel($channel): BelongsToMany
    {
        return $this->belongsToMany(NotificationUser::class, "Notificationtopicsubscriber",
            "NotificationTopicId", "NotificationUserId", "Id", "Id")
            ->withPivot('Channel')
            ->withTimestamps()
            ->wherePivot("Channel", $channel);
    }
}
