<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pricegroup extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Pricegroup';
    protected $primaryKey = 'Id';
    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }
}
