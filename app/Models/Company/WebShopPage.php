<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class WebShopPage extends BaseModel
{
    use HasSlug;

    protected $connection = 'mysql_company';
    protected $table = 'WebShopPage';

    protected $appends = [
        'element_type'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('Name')
            ->saveSlugsTo('SystemKey');
    }

    public function pageGroup($menu_position)
    {
        return $this->pageGroups()->where('MenuPosition', $menu_position)->first();
    }

    public function pageGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            WebShopPageGroup::class,
            'WebShopPageGroupWebShopPage',
            'WebShopPageId',
            'WebShopPageGroupId'
        )->withTimestamps()->withPivot(['Priority']);
    }

    public function getElementTypeAttribute(): string
    {
        return 'WebShopPage';
    }

    public function webShopText()
    {
        return $this->hasMany(WebShopText::class, "ElementNumber", "Id")
            ->where("ElementType", "Page");
    }
}
