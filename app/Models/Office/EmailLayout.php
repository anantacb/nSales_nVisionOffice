<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailLayout extends BaseModel
{
    protected $table = 'EmailLayout';

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'LanguageId', 'Id');
    }

    public function emailTemplate(): HasMany
    {
        return $this->hasMany(EmailTemplate::class, 'LayoutId', 'Id');
    }

}
