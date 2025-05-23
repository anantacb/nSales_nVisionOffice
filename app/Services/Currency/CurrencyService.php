<?php

namespace App\Services\Currency;

use Akaunting\Money\Money;
use App\Models\Office\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return mixed
     */
    public static function convert(float $amount, string $from, string $to): mixed
    {
        $currency = Cache::remember(
            'currency_' . $from . '_' . $to,
            Carbon::now()->addHours(24),
            function () use ($from, $to) {
                return Currency::where('From', $from)->where('To', $to)->first();
            }
        );

        if (!$currency) {
            // $selected_company = Session::get('selected_company');
            $selectedCompany = Cache::get('company_' . request()->get('CompanyId'));
            Log::debug("Error in Currency Conversion. Company Id: $selectedCompany->Id From: $from to: $to");
            return $amount;
        }

        return $amount * $currency->Rate;
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return mixed
     */
    public static function formatAmount(float $amount, string $currency): mixed
    {
        return Money::$currency($amount, true)->formatSimple();
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return mixed
     */
    public static function formatAmountWithCurrency(float $amount, string $currency): mixed
    {
        return Money::$currency($amount, true)->format();
    }

    /**
     * @param string $currency
     * @return string
     */
    public static function formattedCurrency(string $currency): string
    {
        $eur_currencies = ["SEU", "FEU", "TEU"];

        if (in_array($currency, $eur_currencies)) {
            return "EUR";
        }
        return $currency;
    }
}
