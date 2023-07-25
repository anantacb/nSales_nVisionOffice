<?php

namespace App\Models;

use App\Models\Traits\ModelHelper;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use ModelHelper, HasFactory;

    public const CREATED_AT = "InsertTime";
    public const UPDATED_AT = "UpdateTime";
    protected $primaryKey = "Id";
    protected $guarded = [];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
