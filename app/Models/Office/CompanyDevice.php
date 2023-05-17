<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDevice extends BaseModel
{
    use HasFactory;

    protected $table = 'CompanyDevice';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
