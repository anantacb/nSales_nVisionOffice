<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class ItemBackup extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ItemBackup';
}
