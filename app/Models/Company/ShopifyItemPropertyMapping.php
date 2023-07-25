<?php

namespace App\Models\Company;


use App\Models\BaseModel;

class ShopifyItemPropertyMapping extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyItemPropertyMapping';
}
