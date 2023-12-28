<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface OrderByCustomerServiceInterface
{
    public function latestOrdersByCustomer(Request $request): ServiceDto;
}
