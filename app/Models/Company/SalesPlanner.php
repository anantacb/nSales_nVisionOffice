<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPlanner extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'SalesPlanner';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerAccount', 'Account');
    }
}
