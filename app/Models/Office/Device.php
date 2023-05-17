<?php

namespace App\Models\Office;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends BaseModel
{
    use HasFactory;

    protected $table = 'Device';

    public function companyDevice(): HasOne
    {
        return $this->hasOne(CompanyDevice::class, 'DeviceId', 'Id');
    }

    public function companyDevices(): HasMany
    {
        return $this->hasMany(CompanyDevice::class, 'DeviceId', 'Id');
    }
}
