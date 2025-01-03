<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyEmailLayout extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CompanyEmailLayout';

    public function companyLanguage(): BelongsTo
    {
        return $this->belongsTo(CompanyLanguage::class, 'LanguageId', 'Id');
    }

    public function companyEmailTemplate(): HasMany
    {
        return $this->hasMany(CompanyEmailTemplate::class, 'LayoutId', 'Id');
    }

}
