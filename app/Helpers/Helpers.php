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
}
