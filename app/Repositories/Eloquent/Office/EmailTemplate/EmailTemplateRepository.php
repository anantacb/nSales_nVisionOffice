<?php

namespace App\Repositories\Eloquent\Office\EmailTemplate;

use App\Models\Office\EmailTemplate;
use App\Repositories\Eloquent\Base\BaseRepository;

class EmailTemplateRepository extends BaseRepository implements EmailTemplateRepositoryInterface
{
    public function __construct(EmailTemplate $model)
    {
        parent::__construct($model);
    }
}
