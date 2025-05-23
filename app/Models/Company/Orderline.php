<?php

namespace App\Models\Company;

use App\Helpers\FileUrlGenerator;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orderline extends BaseModel
{
    public $incrementing = false;
    public $keyType = "string";
    protected $connection = 'mysql_company';
    protected $table = 'Orderline';
    protected $primaryKey = "UUID";
    protected $appends = ['image_urls'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orderhead::class, 'OrderUUID', 'UUID');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }

    public function getImageUrlsAttribute()
    {
        return FileUrlGenerator::getItemImageUrlByElementNumber($this->ItemNumber);
    }
}
