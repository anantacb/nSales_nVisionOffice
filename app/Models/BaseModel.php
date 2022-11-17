<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $primaryKey = "Id";
    protected $guarded = [];

    public const CREATED_AT = "InsertTime";
    public const UPDATED_AT = "UpdateTime";
}
