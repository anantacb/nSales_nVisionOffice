<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class NotificationTopicSubscriber extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notificationtopicsubscriber';
}
