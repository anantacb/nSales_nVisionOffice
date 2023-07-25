<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebShopPageGroupWebShopPage extends BaseModel
{

    protected $connection = 'mysql_company';
    protected $table = 'WebShopPageGroupWebShopPage';

    public function webShopPage(): BelongsTo
    {
        return $this->belongsTo(WebShopPage::class, 'WebShopPageId', 'Id');
    }

    public function webShopPageGroup(): BelongsTo
    {
        return $this->belongsTo(WebShopPageGroup::class, 'WebShopPageGroupId', 'Id');
    }
}
