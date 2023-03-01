<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailConfiguration extends BaseModel
{
    use HasFactory;

    protected $table = 'EmailConfiguration';

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'ModuleId', 'Id');
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

    public function user()
    {
        $this->hasOneThrough(User::class, CompanyUser::class, 'UserId', 'CompanyUserId', 'Id', 'Id');
    }
}
