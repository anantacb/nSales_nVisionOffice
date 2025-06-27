<?php

namespace App\Helpers;

use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Helpers
{
    /**
     * @throws Exception
     */
    public static function connectDB($dbName): void
    {
        try {
            Config::set("database.connections.mysql_company.database", $dbName);
            DB::purge('mysql_company');
            DB::connection('mysql_company')->getPdo();
            Log::info('Connected to DB: ' . $dbName);
        } catch (Exception $exception) {
            Log::error('Error connecting to DB: ' . $exception->getMessage());
            throw new Exception('DB not found', 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function connectCloudSqlDB($company): void
    {
        try {
            $databaseHost = $company->DatabaseHost;
            $databaseUser = $company->DatabaseUser;
            $databasePassword = $company->DatabasePassword;
            if (!empty($databasePassword)) {
                $encrypter = new Encrypter(env('DB_ENCRYPTION_KEY', ''), config('app.cipher'));
                $databasePassword = $encrypter->decrypt($databasePassword);
            }
            $databaseName = $company->DatabaseName;

            if (!empty($databaseHost)) {
                if (!App::environment('production')) {
                    $databaseHost = env('DEV_GOOGLE_SQL_HOST', '10.30.0.13');
                }
                Config::set("database.connections.mysql_company.host", $databaseHost);
            }
            if (!empty($databaseUser)) {
                Config::set("database.connections.mysql_company.username", $databaseUser);
            }
            if (!empty($databasePassword)) {
                Config::set("database.connections.mysql_company.password", $databasePassword);
            }
            if (!empty($databaseName)) {
                Config::set("database.connections.mysql_company.database", $databaseName);
            }
            // dd(Config::get("database.connections.mysql_company"));
            DB::purge('mysql_company');
            DB::connection('mysql_company')->getPdo();
            Log::info('Connected to Cloud SQL DB: ' . $databaseName, $databaseHost);
        } catch (Exception $exception) {
            Log::error('Error connecting to Cloud SQL DB: ' . $exception->getMessage());
            throw new Exception('DB not found', 400);
        }
    }


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
