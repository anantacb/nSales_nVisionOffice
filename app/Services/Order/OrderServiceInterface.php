<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface OrderServiceInterface
{
    public function getOrders(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;
}
