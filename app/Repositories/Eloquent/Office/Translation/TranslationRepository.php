<?php

namespace App\Repositories\Eloquent\Office\Translation;

use App\Models\Office\Translation;
use App\Repositories\Eloquent\Base\BaseRepository;

class TranslationRepository extends BaseRepository implements TranslationRepositoryInterface
{
    public function __construct(Translation $model)
    {
        parent::__construct($model);
    }
}
