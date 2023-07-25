<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemgroupItemgroup extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ItemgroupItemgroup';

    public function itemgroup(): BelongsTo
    {
        return $this->belongsTo(Itemgroup::class, 'ItemgroupId', 'Id');
    }

    public function parentItemgroup(): BelongsTo
    {
        return $this->belongsTo(Itemgroup::class, 'ParentItemgroupId', 'Id');
    }
}
