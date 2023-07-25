<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class NotificationTopicCondition extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notificationtopiccondition';
}
