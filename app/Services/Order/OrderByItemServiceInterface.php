<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface OrderByItemServiceInterface
{
    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalSalesYearly(Request $request): ServiceDto;

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalSalesMonthly(Request $request): ServiceDto;

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalQuantityYearly(Request $request): ServiceDto;

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalQuantityMonthly(Request $request): ServiceDto;

}
