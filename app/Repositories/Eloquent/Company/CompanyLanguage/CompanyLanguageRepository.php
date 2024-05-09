<?php

namespace App\Repositories\Eloquent\Company\CompanyLanguage;

use App\Models\Company\CompanyLanguage;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyLanguageRepository extends BaseRepository implements CompanyLanguageRepositoryInterface
{
    public function __construct(CompanyLanguage $model)
    {
        parent::__construct($model);
    }
}
