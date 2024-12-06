<?php

namespace App\Repositories\Eloquent\Company\WebShopUser;

use App\Models\Company\WebShopUser;
use App\Repositories\Eloquent\Base\BaseRepository;

class WebShopUserRepository extends BaseRepository implements WebShopUserRepositoryInterface
{
    public function __construct(WebShopUser $model)
    {
        parent::__construct($model);
    }
}
