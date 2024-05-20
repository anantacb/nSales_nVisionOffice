<?php

namespace App\Repositories\Eloquent\Office\Language;

use App\Models\Office\Language;
use App\Repositories\Eloquent\Base\BaseRepository;

class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }
}
