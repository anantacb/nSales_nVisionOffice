<?php

namespace App\Models\Office;

use App\Models\BaseModel;

class Theme extends BaseModel
{
    protected $table = 'Theme';

    public function parent()
    {
        return $this->belongsTo(Theme::class, "ParentId", "Id");
    }

    public function companyTheme()
    {
        return $this->hasMany(CompanyTheme::class, 'ThemeId', 'Id');
    }
}
