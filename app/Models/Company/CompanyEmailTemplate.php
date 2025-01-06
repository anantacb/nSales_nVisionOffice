<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use App\Services\Company\CompanyService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyEmailTemplate extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CompanyEmailTemplate';
    protected $appends = ['ModifiedElementName'];

    public function getModifiedElementNameAttribute()
    {
        $emailEvents = json_decode(CompanyService::getSettingValue('CompanyEmail', 'EmailEvents'), true);
        return $emailEvents[$this->ElementName]['Title'] ?? $this->ElementName;
    }

    public function companyLanguage(): BelongsTo
    {
        return $this->belongsTo(CompanyLanguage::class, 'LanguageId', 'Id');
    }

    public function companyEmailLayout(): BelongsTo
    {
        return $this->belongsTo(CompanyEmailLayout::class, 'LayoutId', 'Id');
    }
}
