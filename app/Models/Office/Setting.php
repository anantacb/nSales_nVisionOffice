<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use App\Services\Company\CompanyService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends BaseModel
{
    protected $table = 'Setting';

    protected static function booted(): void
    {
        $forget = function (Setting $setting) {
            if ($setting->CompanyId) {
                CompanyService::forgetCompanyCache((int) $setting->CompanyId);
            }
        };
        static::saved($forget);
        static::deleted($forget);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }

    public function moduleSetting(): BelongsTo
    {
        return $this->belongsTo(ModuleSetting::class, 'ModuleSettingId', 'Id');
    }
}
