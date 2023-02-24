<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailConfiguration extends BaseModel
{
    use HasFactory;

    protected $table = 'EmailConfiguration';

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'Id');
    }

    public function application(): BelongsTo {
        return $this->belongsTo(Application::class, 'ModuleId', 'Id');
    }
}
