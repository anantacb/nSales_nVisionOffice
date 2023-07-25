<?php

namespace App\Models\Company;

use App\Casts\Base64;
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

    /**
     * SignatureImage is creating UTF-8 encoding format problem so excluded
     * Malformed UTF-8 characters, possibly incorrectly encoded -- Error Message
     */
    //protected $hidden = ['SignatureImage'];

    protected $casts = [
        'SignatureImage' => Base64::class
    ];

}
