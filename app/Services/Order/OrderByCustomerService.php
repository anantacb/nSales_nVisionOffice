<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class OrderByCustomerService implements OrderByCustomerServiceInterface
{
    protected OrderRepositoryInterface $orderRepository;
    protected OrderService $orderService;

    public function __construct(OrderRepositoryInterface $orderRepository, OrderService $orderService)
    {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
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
            $this->orderService->modifyOrderType($order);
        }
    }

}
