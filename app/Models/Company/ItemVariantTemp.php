<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\BelongsTo;

class ItemVariantTemp extends BaseModel
{
    use Compoships;

    protected $connection = 'mysql_company';
    protected $table = 'ItemVariantTemp';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNo', 'Number');
    }
}
