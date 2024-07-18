<?php

namespace App\Repositories\Eloquent\Office\ImageHostAccount;

use App\Models\Office\ImageHostAccount;
use App\Repositories\Eloquent\Base\BaseRepository;

class ImageHostAccountRepository extends BaseRepository implements ImageHostAccountRepositoryInterface
{
    public function __construct(ImageHostAccount $model)
    {
        parent::__construct($model);
    }
}
