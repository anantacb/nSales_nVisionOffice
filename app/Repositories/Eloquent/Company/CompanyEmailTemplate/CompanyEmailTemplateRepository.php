<?php

namespace App\Repositories\Eloquent\Company\CompanyEmailTemplate;

use App\Models\Company\CompanyEmailTemplate;
use App\Repositories\Eloquent\Base\BaseRepository;

class CompanyEmailTemplateRepository extends BaseRepository implements CompanyEmailTemplateRepositoryInterface
{
    public function __construct(CompanyEmailTemplate $model)
    {
        parent::__construct($model);
    }
}
