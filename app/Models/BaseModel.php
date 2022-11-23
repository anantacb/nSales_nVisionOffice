<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public const CREATED_AT = "InsertTime";
    public const UPDATED_AT = "UpdateTime";
    protected $primaryKey = "Id";
    protected $guarded = [];
    protected $casts = [
        'InsertTime' => 'datetime',
        'UpdateTime' => 'datetime'
    ];
}
