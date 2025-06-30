<?php

namespace App\Repositories\Eloquent\Company\Order;

use App\Models\Company\Orderline;
use App\Repositories\Eloquent\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    public function __construct(Orderline $model)
    {
        parent::__construct($model);
    }

    /**
     * @param Request $request
     * @param $startDate
     * @param $endDate
     * @return Collection
     */
    public function salesByDatesByItem(Request $request, $startDate, $endDate): Collection
    {
        return $this->model->join('Orderhead', function ($join) use ($request, $startDate, $endDate) {
            $join->on('Orderline.OrderUUID', '=', 'Orderhead.UUID')
                ->whereBetween('OrderDate', [$startDate, $endDate])
                ->whereIn('Status', ['Closed', 'Sent'])
                ->where(function ($q) use ($request) {
                    if ($request->get('Initials')) {
                        $q->where('Employee', $request->get('Initials'));
                    }
                });
        })->join('Item', function ($join) use ($request) {
            $join->on('Orderline.ItemNumber', '=', 'Item.Number')
                ->where('Item.Id', $request->get('ItemId'));
        })
            ->select(DB::raw('sum(Orderline.TotalExVat) as TotalExVat, Orderhead.CustomerCurrency'))
            ->groupBy('Orderhead.CustomerCurrency')
            ->get();
    }

    /**
     * @param Request $request
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function quantityOrderedByDatesByItem(Request $request, $startDate, $endDate): int
    {
        return $this->model->whereHas('item', function ($query) use ($request) {
            $query->where('Id', $request->get('ItemId'));
        })
            ->whereHas('order', function ($query) use ($request, $startDate, $endDate) {
                $query
                    ->whereBetween('OrderDate', [$startDate, $endDate])
                    ->whereIn('Status', ['Closed', 'Sent'])
                    ->where(function ($q) use ($request) {
                        if ($request->get('Initials')) {
                            $q->where('Employee', $request->get('Initials'));
                        }
                    });
            })
            ->sum("QuantityOrdered");
    }

}
