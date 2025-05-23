<?php

namespace App\Models\Office;

use App\Models\BaseModel;

class PostmarkEmailServer extends BaseModel
{
    protected $table = 'PostmarkEmailServer';
    protected $appends = ['ServerLink'];
    protected $casts = [
        'ServerDetails' => 'array'
    ];

    public function getServerLinkAttribute()
    {
        return $this->ServerDetails['ServerLink'];
    }
}
