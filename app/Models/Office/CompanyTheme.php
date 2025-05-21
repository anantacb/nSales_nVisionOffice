<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTheme extends BaseModel
{
    protected $table = 'CompanyTheme';

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'ThemeId', 'Id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
