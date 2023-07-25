<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Campaign';

    public function campaignLines(): HasMany
    {
        return $this->hasMany(CampaignLine::class, 'CampaignNumber', 'Number');
    }

    public function campaignFilters(): HasMany
    {
        return $this->hasMany(CampaignFilter::class, 'CampaignNumber', 'Number');
    }
}
