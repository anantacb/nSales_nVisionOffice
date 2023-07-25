<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class NotificationTarget extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Notificationtarget';
}
