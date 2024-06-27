<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ModuleSetting extends BaseModel
{
    protected $table = 'ModuleSetting';

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class, 'ModuleSettingId', 'Id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'ModuleSettingId', 'Id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }
}
