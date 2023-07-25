<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShopifyCollection extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyCollection';

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            Item::class,
            ShopifyCollectionItem::class,
            'CollectionId',
            'ItemNumber',
            'CollectionId',
            'Number'
        )->withTimestamps();
    }
}
