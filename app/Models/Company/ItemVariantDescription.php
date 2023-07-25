<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVariantDescription extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'ItemVariantDescription';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNo', 'Number');
    }
}
