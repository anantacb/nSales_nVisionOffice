<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebShopShipping extends BaseModel
{

    protected $connection = 'mysql_company';
    protected $table = 'WebShopShipping';

    public function intervals(): HasMany
    {
        return $this->hasMany(WebShopShippingInterval::class, 'WebShopShippingId', 'Id');
    }
}
