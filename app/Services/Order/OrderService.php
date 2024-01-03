<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Order\OrderLineRepositoryInterface;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use App\Services\Company\CompanyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class OrderService implements OrderServiceInterface
{
    protected OrderRepositoryInterface $orderRepository;
    protected OrderLineRepositoryInterface $orderLineRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, OrderLineRepositoryInterface $orderLineRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderLineRepository = $orderLineRepository;
    }

    public function getOrders(Request $request): ServiceDto
    {
        $orders = $this->orderRepository->paginateWithSearchAndSort($request);
        self::modifyOrderData($orders);

        return new ServiceDto("Orders Retrieved Successfully.", 200, $orders);
    }

    /**
     * @param $orders array|Collection|LengthAwarePaginator|null
     */
    public function modifyOrderData(Collection|LengthAwarePaginator|array|null $orders): void
    {
        $orders->getCollection()->transform(function ($order) {
            $order->ReExport = false;
            if ($order->ExportStatus) {
                $order->ReExport = true;
            }

            self::modifyOrderType($order);
            return $order;
        });
    }

    /**
     * @param $order Model|array|Collection
     */
    public function modifyOrderType(Model|Collection|array $order): void
    {
        if ($order->Type == "") {
            if (Cache::get('company_' . request()->get('CompanyId'))->IntegrationType == "Standard") {
                $order->Type = "SalesBuddy App";
            } else {
                $order->Type = "nVision Mobile";
            }
        } elseif ($order->Type == "WebShop") {
            $order->Type = "Webshop";
        }
    }

    public function getOpenOrders(Request $request): ServiceDto
    {
        $orders = $this->orderRepository->paginateWithSearchAndSortOpenOrders($request);
        self::modifyOrderData($orders);

        return new ServiceDto("Orders Retrieved Successfully.", 200, $orders);
    }

    public function getFailedOrders(Request $request): ServiceDto
    {
        $orders = $this->orderRepository->paginateWithSearchAndSortFailedOrders($request);
        self::modifyOrderData($orders);

        return new ServiceDto("Orders Retrieved Successfully.", 200, $orders);
    }

    public function details(Request $request): ServiceDto
    {
        $attributes = [
            ['column' => 'UUID', 'operand' => '=', 'value' => $request->get('UUID')]
        ];

        if ($request->get('initials')) {
            $attributes[] = ['column' => 'Employee', 'operand' => '=', 'value' => $request->get('initials')];
        }

        $relations = ['orderLines' => function ($q) {
            $q->where('Type', '!=', 'Parent');
        }];

        if (CompanyService::isModuleEnabled('WSUser')) {
            array_push($relations, 'webShopUser');
        }

        $order = $this->orderRepository->firstByAttributes($attributes, $relations);
        return new ServiceDto("Order Retrieved Successfully.", 200, $order);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->orderLineRepository->deleteByAttributes([
            ['column' => 'OrderUUID', 'operand' => '=', 'value' => $request->get('UUID')]
        ]);
        $this->orderRepository->deleteByAttributes([
            ['column' => 'UUID', 'operand' => '=', 'value' => $request->get('UUID')]
        ]);

        return new ServiceDto("Order Deleted successfully.", 200, []);
    }

    public function getOrderOriginOptions(): ServiceDto
    {
        //$origins = $this->repository->getEnumValues('OrderOrigin');

        $origins = [
            'nVisionMobile' => 'nVision Mobile',
            'B2BWebshop' => 'B2B',
            'B2BRetailApp' => 'Retail App',
            'Shopify' => 'Shopify',
        ];

        return new ServiceDto("Order Origin Filter Options retrieved successfully.", 200, ['orderOriginsOptions' => $origins]);
    }

    public function reExportOrder(Request $request): ServiceDto
    {
        $order = $this->orderRepository->firstByAttributes([
            ['column' => 'UUID', 'operand' => '=', 'value' => $request->get('UUID')]
        ]);

        $this->orderRepository->update($order, [
            'ExportStatus' => null
        ]);
        return new ServiceDto("Order Re-exported successfully.", 200, $order);
    }

    public function latestOrdersByCustomer(Request $request): ServiceDto
    {
        $orders = $this->orderRepository->latestOrdersByCustomer($request, 20);
        self::modifyOrdersDataForCollection($orders);

        return new ServiceDto("Orders Retrieved Successfully.", 200, $orders);
    }

    /**
     * @param $orders array|Collection|null
     */
    public function modifyOrdersDataForCollection(Collection|array|null $orders): void
    {
        foreach ($orders as $order) {
            self::modifyOrderType($order);
        }
    }

}
