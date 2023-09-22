<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Application extends BaseModel
{
    protected $table = 'Application';

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, ApplicationModule::class, 'ApplicationId', 'ModuleId');
    }
}
