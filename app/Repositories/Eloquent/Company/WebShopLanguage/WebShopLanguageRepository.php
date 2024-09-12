<?php

namespace App\Repositories\Eloquent\Company\WebShopLanguage;

use App\Models\Company\WebShopLanguage;
use App\Repositories\Eloquent\Base\BaseRepository;

class WebShopLanguageRepository extends BaseRepository implements WebShopLanguageRepositoryInterface
{
    public function __construct(WebShopLanguage $model)
    {
        parent::__construct($model);
    }
}
