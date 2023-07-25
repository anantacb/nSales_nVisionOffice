<?php

namespace App\Models\Company;

use App\Casts\Base64;
use App\Models\BaseModel;
use App\Models\Office\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends BaseModel
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerAccount', 'Account');
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class, 'OrderUUID', 'UUID');
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
