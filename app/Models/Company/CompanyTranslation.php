<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTranslation extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CompanyTranslation';

    protected $casts = [
        'Translations' => 'array',
    ];

    public function companyLanguage(): BelongsTo
    {
        return $this->belongsTo(CompanyLanguage::class, 'CompanyLanguageId', 'Id');
    }
}
