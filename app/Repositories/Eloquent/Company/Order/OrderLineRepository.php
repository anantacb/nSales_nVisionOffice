<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Models\Company\Orderline;
use App\Repositories\Eloquent\Base\BaseRepository;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    public function __construct(Orderline $model)
    {
        parent::__construct($model);
    }


}
