<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

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

}
