<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itemattribute extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Itemattribute';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }
}
