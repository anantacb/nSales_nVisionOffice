<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemVariantPimTemp extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ItemVariantPimTemp';

    public function pim(): HasOne
    {
        return $this->hasOne(ItemPimTemp::class, 'Number', 'ItemId');
    }
}
