<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableField extends BaseModel
{
    protected $table = 'TableField';

    protected $casts = [
        'Nullable' => 'boolean',
        'Unique' => 'boolean',
        'PrimaryKey' => 'boolean',
        'AutoIncrement' => 'boolean',
    ];

    public function companyTableFields(): HasMany
    {
        return $this->hasMany(CompanyTableField::class, 'TableFieldId', 'Id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'TableId', 'Id');
    }
}
