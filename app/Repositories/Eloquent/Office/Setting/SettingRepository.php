<?php

namespace App\Repositories\Eloquent\Office\Setting;

use App\Models\Office\Setting;
use App\Repositories\Eloquent\Base\BaseRepository;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }
}
