<?php

namespace App\Models\Company;

//use App\Helpers\FileUrlGenerator;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderFulfillment extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Orderfulfillment';

    public function shopifyOrder(): BelongsTo
    {
        return $this->belongsTo(Orderhead::class, 'FulfillmentOrderId', 'UUID')
            ->where('OrderOrigin', '=', 'Shopify');
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(FulfillmentOrderLines::class, 'FulfillmentId', 'Id');
    }
}
