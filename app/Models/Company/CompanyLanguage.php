<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyLanguage extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CompanyLanguage';

    public function companyTranslations(): HasMany
    {
        return $this->hasMany(CompanyTranslation::class, 'CompanyLanguageId', 'Id');
    }
}
