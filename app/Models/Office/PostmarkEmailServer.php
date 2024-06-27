<?php

namespace App\Models\Office;

use App\Models\BaseModel;

class PostmarkEmailServer extends BaseModel
{
    protected $table = 'PostmarkEmailServer';

    protected $casts = [
        'ServerDetails' => 'array'
    ];
}
