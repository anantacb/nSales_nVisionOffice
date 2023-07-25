<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class SalesPriceAgreement extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'SalesPriceAgreements';
}
