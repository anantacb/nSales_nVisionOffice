<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Helpers
{
    /**
     * @param Carbon $date
     * @return array
     */
    public static function yearIntervalDates(Carbon $date): array
    {
        return [
            // First day of the year with time set to the start of the day
            'startDate' => $date->copy()->startOfYear()->toDateTimeString(),
            // Last day of the year with time set to the end of the day
            'endDate' => $date->copy()->endOfYear()->toDateTimeString()
        ];
    }

    /**
     * @param Carbon $date
     * @return array
     */
    public static function monthIntervalDates(Carbon $date): array
    {
        return [
            // First day of the year with time set to the start of the day
            'startDate' => $date->copy()->startOfMonth()->toDateTimeString(),
            // Last day of the year with time set to the end of the day
            'endDate' => $date->copy()->endOfMonth()->toDateTimeString()
        ];
    }

    public static function getArrayChanges(array $old, array $new): array
    {
        // Normalize by sorting keys for a consistent comparison
        $normalize = function ($array) {
            return array_map(function ($item) {
                ksort($item);
                return $item;
            }, $array);
        };

        $oldNormalized = $normalize($old);
        $newNormalized = $normalize($new);

        // Compare using json-encoded strings to detect full element differences
        $oldSet = array_map('json_encode', $oldNormalized);
        $newSet = array_map('json_encode', $newNormalized);

        $added = array_values(array_map(fn($json) => json_decode($json, true), array_diff($newSet, $oldSet)));
        $removed = array_values(array_map(fn($json) => json_decode($json, true), array_diff($oldSet, $newSet)));

        return [
            'added' => $added,
            'removed' => $removed,
        ];
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getCompanyDataForTemplate(): array
    {
        $selectedCompany = Cache::get('company_' . request()->input('CompanyId'));
        $addressParts = array_filter([
            $selectedCompany['Street'] ?? null,
            $selectedCompany['ZipCode'] ?? null,
            $selectedCompany['City'] ?? null,
        ]);
        return [
            'CompanyName' => $selectedCompany['CompanyName'] ?? null,
            'CompanyStreet' => $selectedCompany['Street'] ?? null,
            'CompanyZipCode' => $selectedCompany['ZipCode'] ?? null,
            'CompanyCity' => $selectedCompany['City'] ?? null,
            'CompanyPhone' => $selectedCompany['PhoneNo'] ?? null,
            'CompanyEmail' => $selectedCompany['Email'] ?? null,
            'CompanyFax' => $selectedCompany['FaxNo'] ?? null,
            'CompanyVatNo' => $selectedCompany['VATNo'] ?? null,
            'CompanyState' => $selectedCompany['State'] ?? null,
            'CompanyAddress' => implode(', ', $addressParts),
            'CompanyCountry' => $selectedCompany['Country'] ?? null,
            'CompanyLogoUrl' => isset($selectedCompany['imageHostAccount']) ?
                $selectedCompany['imageHostAccount']['Home'] . '/logo.png' : '',
        ];
    }

}
