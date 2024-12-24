<?php

namespace App\Repositories\Eloquent\Office\EmailLayout;

use App\Models\Office\EmailLayout;
use App\Repositories\Eloquent\Base\BaseRepository;

class EmailLayoutRepository extends BaseRepository implements EmailLayoutRepositoryInterface
{
    public function __construct(EmailLayout $model)
    {
        parent::__construct($model);
    }
}
