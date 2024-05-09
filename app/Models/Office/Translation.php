<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends BaseModel
{
    protected $table = 'Translation';

    protected $casts = [
        'Translations' => 'array',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'LanguageId', 'Id');
    }
}
