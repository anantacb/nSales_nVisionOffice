<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class Flag extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Flag';
}
