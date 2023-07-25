<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class FulfillmentOrderLines extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'FulfillmentOrderLines';
}
