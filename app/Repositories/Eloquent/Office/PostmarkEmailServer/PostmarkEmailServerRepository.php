<?php

namespace App\Repositories\Eloquent\Office\PostmarkEmailServer;

use App\Models\Office\PostmarkEmailServer;
use App\Repositories\Eloquent\Base\BaseRepository;

class PostmarkEmailServerRepository extends BaseRepository implements PostmarkEmailServerRepositoryInterface
{
    public function __construct(PostmarkEmailServer $model)
    {
        parent::__construct($model);
    }
}
