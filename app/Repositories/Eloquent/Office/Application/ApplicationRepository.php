<?php

namespace App\Repositories\Eloquent\Office\Application;

use App\Models\Office\Application;
use App\Repositories\Eloquent\Base\BaseRepository;

class ApplicationRepository extends BaseRepository implements ApplicationRepositoryInterface
{
    public function __construct(Application $model)
    {
        parent::__construct($model);
    }
}
