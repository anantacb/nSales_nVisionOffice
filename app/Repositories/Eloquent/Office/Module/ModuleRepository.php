<?php

namespace App\Repositories\Eloquent\Office\Module;

use App\Models\Office\Module;
use App\Repositories\Eloquent\Base\BaseRepository;

class ModuleRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Module $model)
    {
        parent::__construct($model);
    }
}
