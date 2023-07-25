<?php

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WebShopUser extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'WebShopUser';
    protected $hidden = ['Password'];

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'Account', 'AccountNumber');
    }

    public function webShopUserOTP(): HasOne
    {
        return $this->hasOne(WebShopUserOTP::class, 'WebShopUserId', 'Id');
    }
}
