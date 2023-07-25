<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTableField extends BaseModel
{


    protected $table = 'CompanyTableField';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
