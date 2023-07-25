<?php

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WebShopUserOTP extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'WebShopUserOTP';

    public function webShopUser(): HasOne
    {
        return $this->hasOne(WebShopUser::class, 'Id', 'WebShopUserId');
    }
}
