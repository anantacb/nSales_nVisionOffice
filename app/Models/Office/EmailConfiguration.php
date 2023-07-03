<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class EmailConfiguration extends BaseModel
{
    use HasFactory;

    protected $table = 'EmailConfiguration';
    protected $appends = ['ApplyTo'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
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

    public function getApplyToAttribute()
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
    }
}
