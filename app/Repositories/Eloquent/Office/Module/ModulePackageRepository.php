<?php

namespace App\Repositories\Eloquent\Office\Module;

use App\Models\Office\ModulePackage;
use App\Repositories\Eloquent\Base\BaseRepository;

class ModulePackageRepository extends BaseRepository implements ModulePackageRepositoryInterface
{
    public function __construct(ModulePackage $model)
    {
        parent::__construct($model);
    }
}
