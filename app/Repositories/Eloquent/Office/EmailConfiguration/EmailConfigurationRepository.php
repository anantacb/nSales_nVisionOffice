<?php

namespace App\Repositories\Eloquent\Office\EmailConfiguration;

use App\Models\Office\EmailConfiguration;
use App\Repositories\Eloquent\Base\BaseRepository;


class EmailConfigurationRepository extends BaseRepository implements EmailConfigurationRepositoryInterface
{
    public function __construct(EmailConfiguration $model)
    {
        parent::__construct($model);
    }
}
