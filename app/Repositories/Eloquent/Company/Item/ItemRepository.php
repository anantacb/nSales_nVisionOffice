<?php

namespace App\Repositories\Eloquent\Company\Item;

use App\Models\Company\Item;
use App\Repositories\Eloquent\Base\BaseRepository;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }
}
