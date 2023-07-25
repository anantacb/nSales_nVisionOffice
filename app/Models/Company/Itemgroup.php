<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Itemgroup extends BaseModel
{
    use HasSlug;

    protected $connection = 'mysql_company';
    protected $table = 'Itemgroup';
    protected $hidden = ['pivot'];
    protected $appends = ['filtered_value_array'];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('Name')
            ->saveSlugsTo('Number');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            Itemgroup::class,
            ItemgroupItemgroup::class,
            'ItemgroupId',
            'ParentItemgroupId'
        );
    }

    public function parent(): HasOne
    {
        return $this->hasOne(ItemgroupItemgroup::class, 'ItemgroupId', 'Id');
    }

    public function children()
    {
        return $this->child()->with('children')->orderBy('Priority');
    }

    public function child(): BelongsToMany
    {
        return $this->belongsToMany(
            Itemgroup::class,
            ItemgroupItemgroup::class,
            'ParentItemgroupId',
            'ItemgroupId'
        )->orderBy('Priority');
    }

    public function childrenWithWebShopTextAndItem(): BelongsToMany
    {
        return $this->child()->with([
            'childrenWithWebShopTextAndItem',
            'products',
            'webShopTexts'
        ])->orderBy('Priority');
    }

    public function itemgroupItemgroup(): HasOne
    {
        return $this->hasOne(ItemgroupItemgroup::class, 'ItemgroupId', 'Id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            Item::class,
            ItemgroupItem::class,
            'ItemgroupId',
            'ItemNumber',
            'Id',
            'Number'
        );
    }

    public function itemsWithPriority(): BelongsToMany
    {
        return $this->belongsToMany(
            Item::class,
            ItemgroupItemPriority::class,
            'ItemgroupSystemKey',
            'ItemNumber',
            'SystemKey',
            'Number'
        )->orderBy('Priority');
    }

    public function webShopTexts(): HasMany
    {
        return $this->hasMany(WebShopText::class, 'ElementNumber', 'Number')
            ->where('ElementType', '=', 'Itemgroup');
    }

    public function getFilteredValueArrayAttribute(): array
    {
        if (empty($this->CustomerFilteredValues)) {
            return [];
        }

        $values = explode(',', $this->CustomerFilteredValues);
        return array_map(function ($value) {
            return [
                'label' => $value,
                'value' => $value
            ];
        },
            $values
        );
    }
}
