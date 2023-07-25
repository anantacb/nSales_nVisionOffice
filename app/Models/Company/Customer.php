<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Customer';

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'CustomerAccount', 'Account');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'CustomerAccount', 'Account');
    }

    public function webShopUser(): HasOne
    {
        return $this->hasOne(WebShopUser::class, 'AccountNumber', 'Account');
    }

    public function liveInvite(): HasOne
    {
        return $this->hasOne(LiveInvites::class, 'CustomerAccount', 'Account');
    }

    public function pdfCatalogueInvite(): HasOne
    {
        return $this->hasOne(PdfCatalogueInvites::class, 'CustomerAccount', 'Account');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'Account', 'Account');
    }
}
