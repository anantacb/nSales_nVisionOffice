<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class DataFilter extends BaseModel
{
    protected $table = 'DataFilter';
    protected $appends = ['ApplyTo'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'TableId', 'Id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'ApplicationId', 'Id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'RoleId', 'Id');
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class, 'CompanyUserId', 'Id');
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, CompanyUser::class, 'Id', 'Id', 'CompanyUserId', 'UserId');
    }

    public function getApplyToAttribute(): string
    {
        $mapping = [
            'ApplicationId' => 'Application',
            'CompanyId' => 'Company',
            'RoleId' => 'Role',
            'CompanyUserId' => 'User',
        ];

        foreach ($mapping as $key => $value) {
            if ($this->$key) {
                return $value;
            }
        }

        return '';
    }
}
