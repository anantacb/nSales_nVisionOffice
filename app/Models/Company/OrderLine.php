<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\FileUrlGenerator;

class OrderLine extends BaseModel
{
    public $incrementing = false;
    public $keyType = "string";
    protected $connection = 'mysql_company';
    protected $table = 'Orderline';
    protected $primaryKey = "UUID";
    protected $appends = ['image_url'];

    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'OrderUUID', 'UUID');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ItemNumber', 'Number');
    }

    public function getImageUrlAttribute()
    {
        return FileUrlGenerator::imageUrlByElementNumber($this->ItemNumber);
    }
}
