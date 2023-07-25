<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PimItemgroupPimItemgroup extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PimItemgroupPimItemgroup';

    public function parentPimItemgroup(): BelongsTo
    {
        return $this->belongsTo(PimItemgroup::class, 'ParentPimItemgroupId', 'Id');
    }
}
