<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;

    public $keyType = 'string';
    public $incrementing = false;
    protected $connection = 'mysql_company';
    protected $table = 'Orderhead';
    protected $primaryKey = "UUID";
}
