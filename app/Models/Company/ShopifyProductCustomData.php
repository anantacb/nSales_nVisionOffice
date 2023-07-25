<?php

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyProductCustomData extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ShopifyProductCustomData';
    protected $fillable = [
        'ItemNumber',
        'Title',
        'Description',
        'Tags',
        'Vendor'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }

    public function setTagsAttribute(array $value): void
    {
        $this->attributes['Tags'] = implode(',', $value);
    }
}
