<?php

namespace App\Models\Company;


use App\Models\BaseModel;

class ShopifyOrderLinePropertyMapping extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyOrderLinePropertyMapping';
}
