<?php

namespace App\Models\Admin;

use App\Models\BaseModel;

class FtpUser extends BaseModel
{
    public $timestamps = false;
    public $incrementing = false;
    protected $connection = 'mysql_admin';
    protected $table = 'ftp_users';
    protected $primaryKey = null;
}
