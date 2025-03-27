<?php

namespace App\Models;

use App\Models\Traits\ModelHelper;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static insert($data)
 * @method static truncate()
 */
abstract class BaseModel extends Model
{
    use ModelHelper, HasFactory;

    public const CREATED_AT = "InsertTime";
    public const UPDATED_AT = "UpdateTime";

    public const DELETED_AT = "DeleteTime";

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
