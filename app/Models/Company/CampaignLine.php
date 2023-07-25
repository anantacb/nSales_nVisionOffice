<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLine extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'CampaignLine';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'CampaignNumber', 'Number');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }
}
