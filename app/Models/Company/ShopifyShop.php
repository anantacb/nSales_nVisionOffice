<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class ShopifyShop extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyShop';
    protected $appends = ['Name'];

    public function getNameAttribute(): bool|string
    {
        return strtok($this->ShopUrl, '.');
    }
}
