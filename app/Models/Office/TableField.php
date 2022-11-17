<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableField extends BaseModel
{
    use HasFactory;

    protected $table = 'TableField';

    public function companyTableFields(): HasMany
    {
        return $this->hasMany(CompanyTableField::class, 'TableFieldId', 'Id');
    }
}
