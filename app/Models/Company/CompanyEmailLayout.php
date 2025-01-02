<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use App\Models\Office\Language;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyEmailLayout extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CompanyEmailLayout';

    public function companyLanguage(): BelongsTo
    {
        return $this->belongsTo(CompanyLanguage::class, 'LanguageId', 'Id');
    }

//    public function companyEmailTemplate(): HasMany
//    {
//        return $this->hasMany(CompanyEmailTemplate::class, 'LayoutId', 'Id');
//    }
}
