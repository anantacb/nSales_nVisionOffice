<?php

namespace App\Repositories\Eloquent\Office\OnboardStatus;

use App\Models\Office\OnboardStatus;
use App\Repositories\Eloquent\Base\BaseRepository;

class OnboardStatusRepository extends BaseRepository implements OnboardStatusRepositoryInterface
{
    public function __construct(OnboardStatus $model)
    {
        parent::__construct($model);
    }
}
