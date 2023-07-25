<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveInvites extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'LiveInvites';

    public function live(): BelongsTo
    {
        return $this->belongsTo(Live::class, 'LiveNumber', 'Number');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Live::class, 'CustomerAccount', 'Account');
    }
}
