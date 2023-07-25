<?php

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveLine extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'LiveLine';

    public function live(): BelongsTo
    {
        return $this->belongsTo(Live::class, 'LiveNumber', 'Number');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }
}
