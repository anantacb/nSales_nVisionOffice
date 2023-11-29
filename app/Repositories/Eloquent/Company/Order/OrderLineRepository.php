<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Models\Company\OrderLine;
use App\Repositories\Eloquent\Base\BaseRepository;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    public function __construct(OrderLine $model)
    {
        parent::__construct($model);
    }


}
