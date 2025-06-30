<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Models\Company\Orderhead;
use App\Repositories\Eloquent\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Orderhead $model)
    {
        parent::__construct($model);
    }

    /**
     * @param Request $request
     * @return Collection|LengthAwarePaginator
     */
    public function paginateWithSearchAndSort(Request $request): Collection|LengthAwarePaginator
    {
        $query = $this->getBaseQuery($request);
        $query = $query->whereIn('Status', ['Sent', 'Closed'])
            ->where(function ($q) {
                $q->where('ExportStatus', '!=', 'Error')
                    ->orWhereNull('ExportStatus');
            });
        return $this->getFilteredQueryAndPaginatedResult($request, $query);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getBaseQuery(Request $request): mixed
    {
        $query = $this->model
            ->select('*', DB::raw('CASE WHEN UpdateTime IS NULL THEN InsertTime ELSE UpdateTime END AS CalculatedOrderDate'));

        if ($request->has('sort')) {
            $query = $query->orderBy($request->get('sort')['field'], $request->get('sort')['type']);
        }

        if ($request->has('initials')) {
            $query = $query->where('Employee', $request->get('initials'));
        }

        // Search
        $query = $this->getSearch($request, $query);

        // Order
        $query = $this->getOrder($request, $query);

        return $query;
    }

    /**
     * @param Request $request
     * @param $query
     * @return mixed
     */
    private function getFilteredQueryAndPaginatedResult(Request $request, $query): mixed
    {
        if ($request->has('filters')) {
            $filters = $request->get('filters');

            $query = $query->where(function ($que) use ($filters) {
                // OrderOrigin filter
                if (isset($filters['OrderOrigin'])) {
                    $que->where('OrderOrigin', $filters['OrderOrigin']);
                }
                // ExportStatus filter
                if (isset($filters['ExportStatus'])) {
                    if ($filters['ExportStatus'] == 'NotExported') {
                        $que->whereNull('ExportStatus');
                    } elseif ($filters['ExportStatus'] == 'Exported') {
                        $que->where(function ($qu) use ($filters) {
                            $qu->whereNotNull('ExportStatus')
                                ->orWhere('ExportStatus', '!=', '');
                        });
                    }
                }
            });

            // general filter
            if (isset($filters['general'])) {
                if ($filters['general'] == 'NewestFirst') {
                    $query = $query->orderByDesc('CalculatedOrderDate');
                } elseif ($filters['general'] == 'OldestFirst') {
                    $query = $query->orderBy('CalculatedOrderDate');
                } elseif ($filters['general'] == 'OrderTotalHighest') {
                    $query = $query->orderByDesc('TotalExVat');
                } elseif ($filters['general'] == 'OrderTotalLowest') {
                    $query = $query->orderBy('TotalExVat');
                }
            } else {
                $query = $query->orderByDesc('CalculatedOrderDate');
            }

        } else {
            $query = $query->orderByDesc('CalculatedOrderDate');
        }

        //pagination
        return $this->getPagination($request, $query);
    }

    /**
     * @param Request $request
     * @return Collection|LengthAwarePaginator
     */
    public function paginateWithSearchAndSortOpenOrders(Request $request): Collection|LengthAwarePaginator
    {
        $query = $this->getBaseQuery($request);
        $query = $query->whereIn('Status', ['Open']);
        return $this->getFilteredQueryAndPaginatedResult($request, $query);
    }

    /**
     * @param Request $request
     * @return Collection|LengthAwarePaginator
     */
    public function paginateWithSearchAndSortFailedOrders(Request $request): Collection|LengthAwarePaginator
    {
        $query = $this->getBaseQuery($request);
        $query = $query->whereIn('Status', ['Sent', 'Closed'])
            ->where('ExportStatus', 'Error');
        return $this->getFilteredQueryAndPaginatedResult($request, $query);
    }

    /**
     * @param Request $request
     * @param int $ordersLimit
     * @return Collection
     */
    public function latestOrdersByCustomer(Request $request, int $ordersLimit = 20): Collection
    {
        return $this->model->select('UUID', 'OrderDate', 'TotalExVat', 'OrderNumber', 'ExportStatus', 'Type', 'CustomerCurrency')
            ->whereHas('customer', function ($query) use ($request) {
                $query->where('Id', $request->get('CustomerId'));
            })
            /*->where(function ($q) use ($request) {
                if ($request->get('initials')) {
                    $q->where('Employee', $request->get('initials'));
                }
            })*/
            ->latest()->limit($ordersLimit)->get();
    }

}
