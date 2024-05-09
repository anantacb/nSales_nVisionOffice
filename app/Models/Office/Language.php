<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends BaseModel
{
    protected $table = 'Language';

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'LanguageId', 'Id');
    }
}
