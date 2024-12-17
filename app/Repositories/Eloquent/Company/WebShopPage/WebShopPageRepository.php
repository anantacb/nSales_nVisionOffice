<?php

namespace App\Repositories\Eloquent\Company\WebShopPage;

use App\Models\Company\WebShopPage;
use App\Repositories\Eloquent\Base\BaseRepository;

class WebShopPageRepository extends BaseRepository implements WebShopPageRepositoryInterface
{
    public function __construct(WebShopPage $model)
    {
        parent::__construct($model);
    }
}
