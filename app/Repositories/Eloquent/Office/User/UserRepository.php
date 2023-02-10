<?php

namespace App\Repositories\Eloquent\Office\User;

use App\Models\Office\User;
use App\Repositories\Eloquent\Base\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
