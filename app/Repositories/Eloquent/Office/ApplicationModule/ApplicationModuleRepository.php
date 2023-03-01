<?php

namespace App\Repositories\Eloquent\Office\ApplicationModule;

use App\Models\Office\ApplicationModule;
use App\Repositories\Eloquent\Base\BaseRepository;

class ApplicationModuleRepository extends BaseRepository implements ApplicationModuleRepositoryInterface
{
    public function __construct(ApplicationModule $model)
    {
        parent::__construct($model);
    }
}
