<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


        try {
            DB::connection('mysql_company')->getPdo();
        } catch (Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e);
        }

        /*$request['relations'] = [
            ["name" => "module", "columns" => ['Id', 'Name']],
        ];*/
        $orders = $this->orderRepository->paginatedData($request);
        return new ServiceDto("Orders retrieved!!!", 200, $orders);
    }

    public function details(Request $request): ServiceDto
    {
        $company = $this->orderRepository->firstByAttributes([

        ]);
        return new ServiceDto("Order Retrieved Successfully.", 200, $company);
    }
}
