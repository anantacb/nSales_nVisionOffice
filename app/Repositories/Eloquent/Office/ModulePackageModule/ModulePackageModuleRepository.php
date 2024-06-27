<?php

namespace App\Repositories\Eloquent\Office\ModulePackageModule;

use App\Models\Office\ModulePackageModule;
use App\Repositories\Eloquent\Base\BaseRepository;

class ModulePackageModuleRepository extends BaseRepository implements ModulePackageModuleRepositoryInterface
{
    public function __construct(ModulePackageModule $model)
    {
        parent::__construct($model);
    }
}
