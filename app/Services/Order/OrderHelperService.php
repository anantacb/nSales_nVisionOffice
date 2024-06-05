<?php

namespace App\Services\Order;

use App\Services\Currency\CurrencyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as Collect;

abstract class OrderHelperService
{
    /**
     * @param Collection $collection
     * @param string $companyDefaultCurrency
     * @return float
     */
    protected function totalAmount(Collection $collection, string $companyDefaultCurrency): float
    {
        return $collection->reduce(
            function ($carry, $item) use ($companyDefaultCurrency) {
                return $carry +
                    CurrencyService::convert(
                        $item->TotalExVat,
                        $item->CustomerCurrency ?
                            CurrencyService::formattedCurrency($item->CustomerCurrency) : $companyDefaultCurrency,
                        $companyDefaultCurrency
                    );
            },
            0
        );
    }

    /**
     * @param Collect $collection
     * @param string $companyDefaultCurrency
     * @return float
     */
    protected function totalAmountArray(Collect $collection, string $companyDefaultCurrency): float
    {
        return $collection->reduce(function ($carry, $value) use ($companyDefaultCurrency) {
            return $carry +
                CurrencyService::convert(
                    $value['TotalExVat'],
                    $value['CustomerCurrency'] ?
                        CurrencyService::formattedCurrency($value['CustomerCurrency']) : $companyDefaultCurrency,
                    $companyDefaultCurrency
                );
        }, 0);
    }

    /**
     * @param $amount
     * @param $currency
     * @return array
     */
    protected function amountCurrencyFormat($amount, $currency): array
    {
        return [
            "Total" => CurrencyService::formatAmount($amount, $currency),
            "Currency" => $currency,
        ];
    }

}
