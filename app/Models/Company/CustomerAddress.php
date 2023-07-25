<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class CustomerAddress extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CustomerAddress';

    protected $casts = [
        'ReplaceDefault' => 'boolean',
    ];
}
