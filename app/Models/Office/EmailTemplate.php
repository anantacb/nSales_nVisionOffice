<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends BaseModel
{
    protected $table = 'EmailTemplate';
//    protected $appends = ['ElementNameValue'];


//    public function getElementNameValueAttribute()
//    {
//        $emailEvents = json_decode(Helpers::getSettingByModuleAndKey('CompanyEmail', 'EmailEvents'), true);
//        return $emailEvents[$this->ElementName]['Title'] ?? $this->ElementName;
//    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'LanguageId', 'Id');
    }

    public function emailLayout(): BelongsTo
    {
        return $this->belongsTo(EmailLayout::class, 'LayoutId', 'Id');
    }
}
