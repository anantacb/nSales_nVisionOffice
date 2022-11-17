<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends BaseModel
{
    use HasFactory;

    protected $table = 'Table';

    public function companyTables(): HasMany
    {
        return $this->hasMany(CompanyTable::class, 'TableId', 'Id');
    }
}
