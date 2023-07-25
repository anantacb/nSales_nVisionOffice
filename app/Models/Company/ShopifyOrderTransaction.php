<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class ShopifyOrderTransaction extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyOrderTransaction';
}
