<?php

namespace App\Services\Order;

use App\Contracts\ServiceDto;
use App\Helpers\Helpers;
use App\Repositories\Eloquent\Company\Order\OrderLineRepositoryInterface;
use App\Repositories\Eloquent\Company\Order\OrderRepositoryInterface;
use App\Services\Currency\CurrencyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OrderByItemService extends OrderHelperService implements OrderByItemServiceInterface
{
    private const TIMEZONE = 'CET';

    public function __construct(
        protected OrderRepositoryInterface     $orderRepository,
        protected OrderLineRepositoryInterface $orderLineRepository
    )
    {
    }

    public function totalSalesYearly(Request $request): ServiceDto
    {
        ['currency' => $currency, 'date' => $now] = $this->getCompanyData();

        $data = $this->processYearlyData(
            $request,
            $now,
            fn($query) => $this->formatSalesAmount($this->totalAmount($query, $currency), $currency)
        );

        return new ServiceDto('Yearly sales retrieved successfully.', 200,
            [
                'currentYearSales' => $data['current'],
                'lastYearSales' => $data['last']
            ]
        );
    }

    private function getCompanyData(): array
    {
        $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));
        return [
            'company' => $selectedCompany,
            'currency' => CurrencyService::formattedCurrency($selectedCompany->DefaultCurrency),
            'date' => Carbon::now(self::TIMEZONE)
        ];
    }

    private function processYearlyData(Request $request, Carbon $date, callable $formatter): array
    {
        $currentData = $this->getSalesData('yearIntervalDates', $request, $date);
        $lastData = $this->getSalesData('yearIntervalDates', $request, $date->copy()->subYear());

        return [
            'current' => $formatter($currentData),
            'last' => $formatter($lastData)
        ];
    }

    private function getSalesData($intervalMethod, Request $request, Carbon $date): ?Collection
    {
        ['startDate' => $startDate, 'endDate' => $endDate] = Helpers::$intervalMethod($date);
        return $this->orderLineRepository->salesByDatesByItem($request, $startDate, $endDate);
    }

    private function formatSalesAmount(?float $amount, string $currency): ?array
    {
        return $amount ? $this->amountCurrencyFormat($amount, $currency) : null;
    }

    public function totalSalesMonthly(Request $request): ServiceDto
    {
        ['currency' => $currency, 'date' => $now] = $this->getCompanyData();

        $data = $this->processMonthlyData(
            $request,
            $now,
            fn($query) => $this->formatSalesAmount($this->totalAmount($query, $currency), $currency)
        );

        return new ServiceDto('Monthly sales retrieved successfully.', 200,
            [
                'currentYearCurrentMonthSales' => $data['current'],
                'lastYearCurrentMonthSales' => $data['last']
            ]
        );
    }

    private function processMonthlyData(Request $request, Carbon $date, callable $formatter): array
    {
        $currentData = $this->getSalesData('monthIntervalDates', $request, $date);
        $lastData = $this->getSalesData('monthIntervalDates', $request, $date->copy()->subYear());

        return [
            'current' => $formatter($currentData),
            'last' => $formatter($lastData)
        ];
    }

    public function totalQuantityYearly(Request $request): ServiceDto
    {
        $now = Carbon::now(self::TIMEZONE);
        $currentYearOrders = $this->getQuantityData('yearIntervalDates', $request, $now);
        $lastYearOrders = $this->getQuantityData('yearIntervalDates', $request, $now->copy()->subYear());

        return new ServiceDto('Products yearly ordered quantity retrieved successfully.', 200,
            [
                'currentYearOrders' => ['Total' => $currentYearOrders],
                'lastYearOrders' => ['Total' => $lastYearOrders]
            ]
        );
    }

    private function getQuantityData($intervalMethod, Request $request, Carbon $date): float
    {
        ['startDate' => $startDate, 'endDate' => $endDate] = Helpers::$intervalMethod($date);
        return $this->orderLineRepository->quantityOrderedByDatesByItem($request, $startDate, $endDate);
    }

    public function totalQuantityMonthly(Request $request): ServiceDto
    {
        $now = Carbon::now(self::TIMEZONE);
        $currentMonthOrders = $this->getQuantityData('monthIntervalDates', $request, $now);
        $lastYearMonthOrders = $this->getQuantityData('monthIntervalDates', $request, $now->copy()->subYear());

        return new ServiceDto('Products monthly ordered quantity retrieved successfully.', 200,
            [
                'currentYearCurrentMonthOrders' => ['Total' => $currentMonthOrders],
                'lastYearCurrentMonthOrders' => ['Total' => $lastYearMonthOrders]
            ]
        );
    }

}
