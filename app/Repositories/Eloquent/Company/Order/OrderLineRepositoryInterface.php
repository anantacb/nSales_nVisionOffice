<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Repositories\Eloquent\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface OrderLineRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param Request $request
     * @param $startDate
     * @param $endDate
     * @return Collection
     */
    public function salesByDatesByItem(Request $request, $startDate, $endDate): Collection;

    /**
     * @param Request $request
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function quantityOrderedByDatesByItem(Request $request, $startDate, $endDate): int;

}
