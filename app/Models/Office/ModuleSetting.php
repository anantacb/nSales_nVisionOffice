<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModuleSetting extends BaseModel
{
    use HasFactory;

    protected $table = 'ModuleSetting';

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class, 'ModuleSettingId', 'Id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }
}
