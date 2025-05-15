<?php

namespace App\Models\Company;

use App\Casts\Base64;
use App\Models\BaseModel;
use App\Models\Office\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orderhead extends BaseModel
{
    public $keyType = 'string';
    public $incrementing = false;
    protected $connection = 'mysql_company';
    protected $table = 'Orderhead';
    protected $primaryKey = "UUID";

    /**
     * SignatureImage is creating UTF-8 encoding format problem | so exclude or Cast
     * Malformed UTF-8 characters, possibly incorrectly encoded -- Error Message
     */
    //protected $hidden = ['SignatureImage'];

    protected $casts = [
        'SignatureImage' => Base64::class
    ];
    protected $appends = ['CalculatedOrderOrigin', 'CalculatedOrderDate'];
    public function getCalculatedOrderOriginAttribute()
    {
        $mapping = [
            'nVisionMobile' => 'nVision Mobile',
            'B2BWebshop' => 'B2B',
            'B2BRetailApp' => 'Retail App',
            'Shopify' => 'Shopify',
        ];

        return isset($mapping[$this->OrderOrigin]) ? $mapping[$this->OrderOrigin] : '';
    }

    public function getCalculatedOrderDateAttribute()
    {
        return $this->UpdateTime ? $this->UpdateTime : $this->InsertTime;
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerAccount', 'Account');
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(Orderline::class, 'OrderUUID', 'UUID');
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }

    public function webShopPaymentGatewayLogs(): HasMany
    {
        return $this->hasMany(WebShopPaymentGateway::class, 'OrderUUID', 'UUID');
    }

    public function webShopUser(): BelongsTo
    {
        return $this->belongsTo(WebShopUser::class, 'WebShopUserId', 'Id');
    }
}
