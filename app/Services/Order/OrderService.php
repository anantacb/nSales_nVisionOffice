<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class OrderService implements OrderServiceInterface
{
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getOrders(Request $request): ServiceDto
    {
        $request = $request->all();


        /*try {
            DB::connection('mysql_company')->getPdo();
        } catch (Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e);
        }*/

        /*$request['relations'] = [
            ["name" => "module", "columns" => ['Id', 'Name']],
        ];*/
        $orders = $this->orderRepository->paginatedData($request);
        return new ServiceDto("Orders retrieved!!!", 200, $orders);
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
}
