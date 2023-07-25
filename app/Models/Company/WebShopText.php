<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebShopText extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'WebShopText';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ElementNumber', 'Number');
    }

    public function itemgroup(): BelongsTo
    {
        return $this->belongsTo(Itemgroup::class, 'ElementNumber', 'SystemKey');
    }

}
