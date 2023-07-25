<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PimText extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PimText';

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ElementNumber', 'Number');
    }
}
