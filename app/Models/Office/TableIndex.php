<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableIndex extends BaseModel
{
    protected $table = 'TableIndex';

    public function companyTableIndices(): HasMany
    {
        return $this->hasMany(CompanyTableIndex::class, 'TableIndexId', 'Id');
    }
}
