<?php

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyImages extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyImages';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }
}
