<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class WebShopPaymentGateway extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'WebShopPaymentGateway';
}
