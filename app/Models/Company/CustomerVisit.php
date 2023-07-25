<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class CustomerVisit extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CustomerVisit';
}
