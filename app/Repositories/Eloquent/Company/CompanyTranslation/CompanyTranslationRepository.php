<?php

namespace App\Repositories\Eloquent\Company\CompanyTranslation;

use App\Models\Company\CompanyTranslation;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyTranslationRepository extends BaseRepository implements CompanyTranslationRepositoryInterface
{
    public function __construct(CompanyTranslation $model)
    {
        parent::__construct($model);
    }
}
