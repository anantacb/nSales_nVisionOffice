<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PdfCatalogue extends BaseModel
{
    use HasSlug;

    protected $connection = 'mysql_company';
    protected $table = 'PdfCatalogue';

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('Name')
            ->saveSlugsTo('Number');
    }

    public function pdfCatalogueInvites(): HasMany
    {
        return $this->hasMany(PdfCatalogueInvites::class, 'PdfCatalogueNumber', 'Number');
    }
}
