<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class PriceListLine extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PriceListLine';
}
