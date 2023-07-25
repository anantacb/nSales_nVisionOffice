<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends BaseModel
{


    protected $table = 'Role';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
