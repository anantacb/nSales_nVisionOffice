<?php

namespace App\Models\Office;

use App\Models\BaseModel;

class CompanyTheme extends BaseModel
{
    protected $table = 'CompanyTheme';

    public function theme()
    {
        return $this->belongsTo(Theme::class, 'ThemeId', 'Id');
    }
}
