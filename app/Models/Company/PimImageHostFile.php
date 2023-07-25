<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class PimImageHostFile extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PimImageHostFile';
}
