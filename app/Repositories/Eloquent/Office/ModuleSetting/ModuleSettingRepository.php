<?php

namespace App\Repositories\Eloquent\Office\ModuleSetting;

use App\Models\Office\ModuleSetting;
use App\Repositories\Eloquent\Base\BaseRepository;

class ModuleSettingRepository extends BaseRepository implements ModuleSettingRepositoryInterface
{
    public function __construct(ModuleSetting $model)
    {
        parent::__construct($model);
    }
}
