<?php

namespace App\Models\Company;

use App\Models\BaseModel;

class FirebaseCloudMessage extends BaseModel
{
    protected const STATUS = [
        "PENDING" => "pending",
        "IN_PROGRESS" => "in_progress",
        "DONE" => "done",
        "FAILED" => 'failed'
    ];
    protected $connection = 'mysql_company';
    protected $table = 'FirebaseCloudMessage';

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS['PENDING']);
    }

    public function scopeProgress($query)
    {
        return $query->where('status', self::STATUS['IN_PROGRESS']);
    }

    public function scopeDone($query)
    {
        return $query->where('status', self::STATUS['DONE']);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS['FAILED']);
    }
}
