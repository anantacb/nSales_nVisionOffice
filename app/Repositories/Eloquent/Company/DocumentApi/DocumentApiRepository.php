<?php

namespace App\Repositories\Eloquent\Company\DocumentApi;

use App\Models\Company\DocumentApi;
use App\Repositories\Eloquent\Base\BaseRepository;

class DocumentApiRepository extends BaseRepository implements DocumentApiRepositoryInterface
{
    public function __construct(DocumentApi $model)
    {
        parent::__construct($model);
    }
}
