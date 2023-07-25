<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class PimImageHost extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PimImageHost';
}
