<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends BaseModel
{
    protected $table = 'Company';
//    protected $fillable = ['CustomDomains']; // Ensure fillable attributes are set
//
//    protected $casts = [
//        'CustomDomains' => 'string',
//    ];
    protected $appends = ['CustomDomainsArray'];

    public function getCustomDomainsArrayAttribute(): array
    {
        return isset($this->CustomDomains) && $this->CustomDomains ? explode(',', $this->CustomDomains) : [];
    }

    public function setCustomDomainsAttribute($value): void
    {
        $this->attributes['CustomDomains'] = is_array($value) ? implode(',', $value) : $value;
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, CompanyModule::class, 'CompanyId', 'ModuleId');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'CompanyId', 'Id');
    }

    public function imageHostAccount(): HasOne
    {
        return $this->hasOne(ImageHostAccount::class, 'CompanyId', 'Id');
    }

    public function postmarkEmailServer(): HasOne
    {
        return $this->hasOne(PostmarkEmailServer::class, 'CompanyId', 'Id');
    }

    public function companyUsers(): HasMany
    {
        return $this->hasMany(CompanyUser::class, 'CompanyId', 'Id');
    }
}
