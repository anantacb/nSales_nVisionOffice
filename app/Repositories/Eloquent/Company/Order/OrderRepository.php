<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Models\Company\Order;
use App\Repositories\Eloquent\Base\BaseRepository;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }
}
