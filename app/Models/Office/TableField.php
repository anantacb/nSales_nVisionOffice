<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableField extends BaseModel
{
    use HasFactory;

    protected $table = 'TableField';

    protected $casts = [
        'InsertTime' => 'datetime:Y-m-d H:i:s',
        'UpdateTime' => 'datetime:Y-m-d H:i:s',
        'Nullable' => 'boolean',
        'Unique' => 'boolean',
        'PrimaryKey' => 'boolean',
        'AutoIncrement' => 'boolean',
    ];

    public function companyTableFields(): HasMany
    {
        return $this->hasMany(CompanyTableField::class, 'TableFieldId', 'Id');
    }
}
