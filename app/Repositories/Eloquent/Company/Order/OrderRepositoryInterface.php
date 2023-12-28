<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Repositories\Eloquent\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param Request $request
     * @return Collection|LengthAwarePaginator
     */
    public function paginateWithSearchAndSort(Request $request): Collection|LengthAwarePaginator;

    /**
     * @param Request $request
     * @return Collection|LengthAwarePaginator
     */
    public function paginateWithSearchAndSortOpenOrders(Request $request): Collection|LengthAwarePaginator;

    /**
     * @param Request $request
     * @return Collection|LengthAwarePaginator
     */
    public function paginateWithSearchAndSortFailedOrders(Request $request): Collection|LengthAwarePaginator;

    /**
     * @param Request $request
     * @param int $ordersLimit
     * @return Collection
     */
    public function latestOrdersByCustomer(Request $request, int $ordersLimit): Collection;

}
