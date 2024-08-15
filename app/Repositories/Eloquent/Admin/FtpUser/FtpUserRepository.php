<?php

namespace App\Repositories\Eloquent\Admin\FtpUser;

use App\Models\Admin\FtpUser;
use App\Repositories\Eloquent\Base\BaseRepository;

class FtpUserRepository extends BaseRepository implements FtpUserRepositoryInterface
{
    public function __construct(FtpUser $model)
    {
        parent::__construct($model);
    }
}
