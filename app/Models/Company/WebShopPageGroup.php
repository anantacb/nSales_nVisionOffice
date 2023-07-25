<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WebShopPageGroup extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'WebShopPageGroup';

    protected $appends = [
        'element_type'
    ];

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(
            WebShopPage::class,
            'WebShopPageGroupWebShopPage',
            'WebShopPageGroupId',
            'WebShopPageId'
        );
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            WebShopPage::class,
            'WebShopPageGroupWebShopPage',
            'WebShopPageGroupId',
            'WebShopPageId'
        )->withPivot(['Priority']);
    }

    public function getElementTypeAttribute(): string
    {
        return 'WebShopPageGroup';
    }
}
