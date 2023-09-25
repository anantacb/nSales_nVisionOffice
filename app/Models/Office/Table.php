<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends BaseModel
{
    protected $table = 'Table';

    public function companyTables(): HasMany
    {
        return $this->hasMany(CompanyTable::class, 'TableId', 'Id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }

    public function tableFields(): HasMany
    {
        return $this->hasMany(TableField::class, 'TableId', 'Id');
    }

    public function tableIndices(): HasMany
    {
        return $this->hasMany(TableIndex::class, 'TableId', 'Id');
    }
}
