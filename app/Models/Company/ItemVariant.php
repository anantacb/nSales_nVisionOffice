<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\BelongsTo;
use Awobaz\Compoships\Database\Eloquent\Relations\HasOne;

class ItemVariant extends BaseModel
{
    use Compoships;

    protected $connection = 'mysql_company';
    protected $table = 'ItemVariant';

    public function color(): HasOne
    {
        return $this->hasOne(ItemVariantColor::class, ['Code', 'GroupCode'], ['ColorCode', 'ColorGroupCode']);
    }

    public function size(): HasOne
    {
        return $this->hasOne(ItemVariantSize::class, ['Code', 'GroupCode'], ['SizeCode', 'SizeGroupCode']);
    }

    public function dimension(): HasOne
    {
        return $this->hasOne(ItemVariantDimension::class, ['Code', 'GroupCode'], ['DimensionCode', 'DimensionGroupCode']);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNo', 'Number');
    }

    public function webShopColor(): BelongsTo
    {
        return $this->belongsTo(WebShopColors::class, 'ColorCode', 'ColorCode');
    }
}
