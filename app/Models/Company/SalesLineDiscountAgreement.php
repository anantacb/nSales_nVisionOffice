<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class SalesLineDiscountAgreement extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'SalesLineDiscountAgreements';
}
