<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class NotificationSchedule extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notificationschedule';
}
