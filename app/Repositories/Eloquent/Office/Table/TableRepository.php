<?php

namespace App\Repositories\Eloquent\Office\Table;

use App\Models\Office\Table;
use App\Repositories\Eloquent\Base\BaseRepository;
use Illuminate\Support\Facades\Cache;

class TableRepository extends BaseRepository implements TableRepositoryInterface
{
    public function __construct(Table $model)
    {
        parent::__construct($model);
    }
}
