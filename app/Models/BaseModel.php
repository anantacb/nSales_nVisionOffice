<?php

namespace App\Models;

use App\Models\Traits\ModelHelper;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use ModelHelper;

    public const CREATED_AT = "InsertTime";
    public const UPDATED_AT = "UpdateTime";
    protected $primaryKey = "Id";
    protected $guarded = [];
    protected $casts = [
        'InsertTime' => 'datetime:Y-m-d H:i:s',
        'UpdateTime' => 'datetime:Y-m-d H:i:s'
    ];
}
