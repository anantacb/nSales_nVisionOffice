<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnOrder extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ReturnOrder';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerAccount', 'Account');
    }

    public function webShopUser(): BelongsTo
    {
        return $this->belongsTo(WebShopUser::class, 'WebshopUserId', 'Id');
    }
}
