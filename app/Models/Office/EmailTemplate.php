<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends BaseModel
{
    protected $table = 'EmailTemplate';

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'LanguageId', 'Id');
    }

    public function emailLayout(): BelongsTo
    {
        return $this->belongsTo(EmailLayout::class, 'LayoutId', 'Id');
    }
}
