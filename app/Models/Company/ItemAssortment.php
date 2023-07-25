<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class ItemAssortment extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ItemAssortment';
}
