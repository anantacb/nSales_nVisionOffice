<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Item';

    //protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        //return FileUrlGenerator::imageUrlByElementNumber($this->Number, $this);
    }

    public function getItemVariantExistsAttribute(): bool
    {
        return $this->itemVariants()->exists();
    }

    public function itemVariants(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'ItemNo', 'Number');
    }

    public function itemVariantDescriptions(): HasMany
    {
        return $this->hasMany(ItemVariantDescription::class, 'ItemNo', 'Number');
    }

    public function priceGroups(): HasMany
    {
        return $this->hasMany(PriceGroup::class, 'ItemNumber', 'Number');
    }

    public function itemAttributes(): HasMany
    {
        return $this->hasMany(Itemattribute::class, 'ItemNumber', 'Number');
    }

    public function itemgroups(): BelongsToMany
    {
        return $this->belongsToMany(
            Itemgroup::class,
            ItemgroupItem::class,
            'ItemNumber',
            'ItemgroupId',
            'Number',
            'Id'
        );
    }

    public function shopifyCollections(): BelongsToMany
    {
        return $this->belongsToMany(
            ShopifyCollection::class,
            ShopifyCollectionItem::class,
            'ItemNumber',
            'CollectionId',
            'Number',
            'CollectionId'
        );
    }


    public function pimItemgroups(): BelongsToMany
    {
        return $this->belongsToMany(
            PimItemgroup::class,
            PimItemgroupItem::class,
            'ItemNumber',
            'PimItemgroupId',
            'Number',
            'Id'
        );
    }

    public function pimAssets(): HasMany
    {
        return $this->hasMany(PimImageHost::class, 'ElementNumber', 'Number')
            ->where('ElementType', 'Item');
    }

    public function shoppingFeeds(): HasMany
    {
        return $this->hasMany(ItemShoppingFeed::class, 'ItemNumber', 'Number');
    }

    public function itemAssortments(): HasMany
    {
        return $this->hasMany(ItemAssortment::class, 'Number', 'Number');
    }

    public function shopifyProduct(): HasOne
    {
        return $this->hasOne(ShopifyProduct::class, 'ItemNumber', 'Number');
    }

    public function shopifyProductVariants(): HasMany
    {
        return $this->hasMany(ShopifyItemVariant::class, 'ItemNumber', 'Number');
    }

    public function shopifyProductCustomData(): HasOne
    {
        return $this->hasOne(ShopifyProductCustomData::class, 'ItemNumber', 'Number');
    }

    public function shopifyProductImages(): HasMany
    {
        return $this->hasMany(ShopifyImages::class, 'ItemNumber', 'Number');
    }

    public function webShopTexts(): HasMany
    {
        return $this->hasMany(WebShopText::class, 'ElementNumber', 'Number')
            ->where('ElementType', '=', 'Item');
    }
}
