<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveOrderSummary extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'LiveOrderSummary';

    public function live(): BelongsTo
    {
        return $this->belongsTo(Live::class, 'LiveNumber', 'Number');
    }
}
