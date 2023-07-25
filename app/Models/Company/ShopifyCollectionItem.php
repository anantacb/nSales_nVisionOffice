<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyCollectionItem extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyCollectionItem';

    public function shopifyProduct(): BelongsTo
    {
        return $this->belongsTo(ShopifyProduct::class, 'ItemNumber', 'ItemNumber');
    }
}
