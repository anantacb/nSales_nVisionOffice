<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PimItemgroup extends BaseModel
{
    use HasSlug;

    protected $connection = 'mysql_company';
    protected $table = 'PimItemgroup';

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
            PimItemgroup::class,
            PimItemgroupPimItemgroup::class,
            'PimItemgroupId',
            'ParentPimItemgroupId'
        );
    }

    public function parent(): HasOne
    {
        return $this->hasOne(PimItemgroupPimItemgroup::class, 'PimItemgroupId', 'Id');
    }

    public function children()
    {
        return $this->child()->with('children')->orderBy('Priority');
    }

    public function child(): BelongsToMany
    {
        return $this->belongsToMany(
            PimItemgroup::class,
            PimItemgroupPimItemgroup::class,
            'ParentPimItemgroupId',
            'PimItemgroupId'
        )->orderBy('Priority');
    }

    public function pimItemgroupPimItemgroup(): HasOne
    {
        return $this->hasOne(PimItemgroupPimItemgroup::class, 'PimItemgroupId', 'Id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            Item::class,
            PimItemgroupItem::class,
            'PimItemgroupId',
            'ItemNumber',
            'Id',
            'Number'
        );
    }

    public function pimAssets(): HasMany
    {
        return $this->hasMany(PimImageHost::class, 'ElementNumber', 'Number')
            ->where('ElementType', 'Itemgroup');
    }
}
