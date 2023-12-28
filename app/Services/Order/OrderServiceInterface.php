<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface OrderServiceInterface
{
    public function getOrders(Request $request): ServiceDto;

    public function getOpenOrders(Request $request): ServiceDto;

    public function getFailedOrders(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function latestOrdersByCustomer(Request $request): ServiceDto;

}
