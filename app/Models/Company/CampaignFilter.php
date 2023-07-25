<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignFilter extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CampaignFilter';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'CampaignNumber', 'Number');
    }
}
