<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Order\OrderLineRepositoryInterface;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use App\Services\Currency\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OrderByItemService extends OrderHelperService implements OrderByItemServiceInterface
{
    protected OrderRepositoryInterface $orderRepository;
    protected OrderLineRepositoryInterface $orderLineRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, OrderLineRepositoryInterface $orderLineRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderLineRepository = $orderLineRepository;
    }

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalSalesYearly(Request $request): ServiceDto
    {
//        $currentYearSales = null;
//        $lastYearSales = null;
        $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));
        $companyDefaultCurrency = CurrencyService::formattedCurrency($selectedCompany->DefaultCurrency);

        list("startDate" => $startDate, "endDate" => $endDate) = yearIntervalDates(Carbon::now('CET'));
        $currentYearSalesQuery = $this->orderLineRepository->salesByDatesByItem($request, $startDate, $endDate);
        $currentYearSalesAmount = $this->totalAmount($currentYearSalesQuery, $companyDefaultCurrency);

        list("startDate" => $startDate, "endDate" => $endDate) = yearIntervalDates(Carbon::now('CET')->subYear());
        $lastYearSalesQuery = $this->orderLineRepository->salesByDatesByItem($request, $startDate, $endDate);
        $lastYearSalesAmount = $this->totalAmount($lastYearSalesQuery, $companyDefaultCurrency);

//        if ($currentYearSalesAmount) {
//            $currentYearSales = $this->amountCurrencyFormat($currentYearSalesAmount, $companyDefaultCurrency);
//        }
//
//        if ($lastYearSalesAmount) {
//            $lastYearSales = $this->amountCurrencyFormat($lastYearSalesAmount, $companyDefaultCurrency);
//        }

        $data = [
            'currentYearSales' => $currentYearSalesAmount ?
                $this->amountCurrencyFormat($currentYearSalesAmount, $companyDefaultCurrency) : null,
            'lastYearSales' => $lastYearSalesAmount ?
                $this->amountCurrencyFormat($lastYearSalesAmount, $companyDefaultCurrency) : null,
        ];

        return new ServiceDto("Yearly sales retrieved successfully.", 200, $data);
    }

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalSalesMonthly(Request $request): ServiceDto
    {
//        $currentYearCurrentMonthSales = null;
//        $lastYearCurrentMonthSales = null;
        $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));
        $companyDefaultCurrency = CurrencyService::formattedCurrency($selectedCompany->DefaultCurrency);

        list("startDate" => $startDate, "endDate" => $endDate) = monthIntervalDates(Carbon::now('CET'));
        $currentYearCurrentMonthSalesQuery = $this->orderLineRepository->salesByDatesByItem($request, $startDate, $endDate);
        $currentYearCurrentMonthSalesAmount = $this->totalAmount($currentYearCurrentMonthSalesQuery, $companyDefaultCurrency);

        list("startDate" => $startDate, "endDate" => $endDate) = monthIntervalDates(Carbon::now('CET')->subYear());
        $lastYearCurrentMonthSalesQuery = $this->orderLineRepository->salesByDatesByItem($request, $startDate, $endDate);
        $lastYearCurrentMonthSalesAmount = $this->totalAmount($lastYearCurrentMonthSalesQuery, $companyDefaultCurrency);

//        if ($currentYearCurrentMonthSalesAmount) {
//            $currentYearCurrentMonthSales = $this->amountCurrencyFormat($currentYearCurrentMonthSalesAmount, $companyDefaultCurrency);
//        }
//
//        if ($lastYearCurrentMonthSalesAmount) {
//            $lastYearCurrentMonthSales = $this->amountCurrencyFormat($lastYearCurrentMonthSalesAmount, $companyDefaultCurrency);
//        }

        $data = [
            'currentYearCurrentMonthSales' => $currentYearCurrentMonthSalesAmount ?
                $this->amountCurrencyFormat($currentYearCurrentMonthSalesAmount, $companyDefaultCurrency) : null,
            'lastYearCurrentMonthSales' => $lastYearCurrentMonthSalesAmount ?
                $this->amountCurrencyFormat($lastYearCurrentMonthSalesAmount, $companyDefaultCurrency) : null,
        ];

        return new ServiceDto("Monthly sales retrieved successfully.", 200, $data);
    }

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalQuantityYearly(Request $request): ServiceDto
    {
        list("startDate" => $startDate, "endDate" => $endDate) = yearIntervalDates(Carbon::now('CET'));
        $currentYearOrders = $this->orderLineRepository->quantityOrderedByDatesByItem($request, $startDate, $endDate);

        list("startDate" => $startDate, "endDate" => $endDate) = yearIntervalDates(Carbon::now('CET')->subYear());
        $lastYearOrders = $this->orderLineRepository->quantityOrderedByDatesByItem($request, $startDate, $endDate);

        $data = [
            'currentYearOrders' => [
                "Total" => $currentYearOrders
            ],
            'lastYearOrders' => [
                "Total" => $lastYearOrders
            ],
        ];

        return new ServiceDto("Products yearly ordered quantity retrieved successfully.", 200, $data);
    }

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function totalQuantityMonthly(Request $request): ServiceDto
    {
        list("startDate" => $startDate, "endDate" => $endDate) = monthIntervalDates(Carbon::now('CET'));
        $currentYearCurrentMonthOrders = $this->orderLineRepository->quantityOrderedByDatesByItem($request, $startDate, $endDate);

        list("startDate" => $startDate, "endDate" => $endDate) = monthIntervalDates(Carbon::now('CET')->subYear());
        $lastYearCurrentMonthOrders = $this->orderLineRepository->quantityOrderedByDatesByItem($request, $startDate, $endDate);

        $data = [
            'currentYearCurrentMonthOrders' => [
                "Total" => $currentYearCurrentMonthOrders
            ],
            'lastYearCurrentMonthOrders' => [
                "Total" => $lastYearCurrentMonthOrders
            ],
        ];

        return new ServiceDto("Products yearly ordered quantity retrieved successfully.", 200, $data);
    }
}
