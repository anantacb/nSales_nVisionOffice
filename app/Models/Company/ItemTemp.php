<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemTemp extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ItemTemp';

    public function translation(): HasOne
    {
        return $this->hasOne(ItemTranslationTemp::class, 'ItemNo', 'Number');
    }

    public function variantPim(): HasOne
    {
        return $this->hasOne(ItemVariantPimTemp::class, 'ItemNumber', 'Number');
    }

    public function suppInfo(): HasMany
    {
        return $this->hasMany(SuppInfoTemp::class, 'ItemNo', 'Number');
    }

    public function tagsAttribute(): HasOne
    {
        return $this->hasOne(Itemattribute::class, 'ItemNumber', 'Number')->where('TypeCode', 'Tags');
    }
}
