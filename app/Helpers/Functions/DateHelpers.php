<?php

use Illuminate\Support\Carbon;

/**
 * @param Carbon $date
 * @return array
 */
function yearIntervalDates(Carbon $date): array
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
function monthIntervalDates(Carbon $date): array
{
    return [
        // First day of the year with time set to the start of the day
        'startDate' => $date->copy()->startOfMonth()->toDateTimeString(),
        // Last day of the year with time set to the end of the day
        'endDate' => $date->copy()->endOfMonth()->toDateTimeString()
    ];
}


if (!function_exists('week_interval_dates')) {
    /**
     * @param string $date Format:Y-m-d
     * @param string $start_day [monday]
     * @param string $end_day [sunday]
     * @return array
     */
    function week_interval_dates(string $date, string $start_day = "monday", string $end_day = "sunday")
    {
        $ts = strtotime($date);
        $start = (date('w', $ts) == 0) ? $ts : strtotime($start_day . ' this week', $ts);
        $start_date = date('Y-m-d', $start);
        $end_date = date('Y-m-d', strtotime($end_day . ' this week', $start));
        return [
            'start_date' => $start_date . ' 00:00:00',
            'end_date' => $end_date . ' 23:59:59'
        ];
    }
}

if (!function_exists('month_interval_dates')) {
    /**
     * @param string $date Format:Y-m-d
     * @return array
     */
    function month_interval_dates(string $date)
    {
        $start_date = date('Y-m-01', strtotime($date));
        $end_date = date('Y-m-t', strtotime($date));
        return [
            'start_date' => $start_date . ' 00:00:00',
            'end_date' => $end_date . ' 23:59:59'
        ];
    }
}

if (!function_exists('year_interval_dates')) {
    /**
     * @param string $date Format:Y-m-d
     * @return array
     */
    function year_interval_dates(string $date, $years = 1)
    {
        $start_date = date('Y-01-01', strtotime($date));
        $end_date = date('Y-12-31', strtotime($date));

        if ($years > 1) {
            $year = date('Y', strtotime($date));
            $year = $year - $years + 1;
            $start_date = date("$year-01-01", strtotime($date));
        }

        return [
            'start_date' => $start_date . ' 00:00:00',
            'end_date' => $end_date . ' 23:59:59'
        ];
    }
}


if (!function_exists('dayIntervalDates')) {
    /**
     * @return array
     */
    function dayIntervalDates(): array
    {
        $today = Carbon::today('CET');
        return [
            'startDate' => $today->copy()->startOfYear()->toDateTimeString(),
            'endDate' => $today->copy()->endOfYear()->toDateTimeString()
        ];
    }
}

if (!function_exists('interval_dates')) {
    /**
     * @return array
     */
    function interval_dates(string $date, $days)
    {
        $start_date = date('Y-m-d', strtotime($date . " -$days years"));
        $end_date = date('Y-m-d', strtotime($date));

        return [
            'start_date' => $start_date . ' 00:00:00',
            'end_date' => $end_date . ' 23:59:59'
        ];
    }
}
