<?php

namespace App\Repositories\Eloquent\Company\WebShopText;

use App\Models\Company\WebShopText;
use App\Repositories\Eloquent\Base\BaseRepository;

class WebShopTextRepository extends BaseRepository implements WebShopTextRepositoryInterface
{
    public function __construct(WebShopText $model)
    {
        parent::__construct($model);
    }
}
