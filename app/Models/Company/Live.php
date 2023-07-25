<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Live extends BaseModel
{
    use HasSlug;

    protected $connection = 'mysql_company';
    protected $table = 'Live';

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('Name')
            ->saveSlugsTo('Number');
    }

    public function liveLines(): HasMany
    {
        return $this->hasMany(LiveLine::class, 'LiveNumber', 'Number');
    }

    public function liveInvites(): HasMany
    {
        return $this->hasMany(LiveInvites::class, 'LiveNumber', 'Number');
    }
}
