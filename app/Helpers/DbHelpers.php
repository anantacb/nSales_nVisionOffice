<?php

namespace App\Helpers;

use App\Services\Company\CompanyService;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbHelpers
{

    public static function getOfficeDatabaseConnectionDetails(): array
    {
        return [
            'CloudSqlMigrated' => 0,
            'DomainName' => '',
            'DatabaseName' => env('DB_DATABASE', 'NVISION_OFFICE'),
            'DatabaseHost' => env('DB_HOST'),
            'DatabaseUser' => env('DB_USERNAME'),
            'DatabasePassword' => env('DB_PASSWORD')
        ];
    }

    public static function getTemplateDatabaseConnectionDetails(): array
    {
        return [
            'CloudSqlMigrated' => 0,
            'DomainName' => '',
            'DatabaseName' => env('DB_TEMPLATE_DATABASE', 'NVISION_TEMPLATE'),
            'DatabaseHost' => env('DB_HOST'),
            'DatabaseUser' => env('DB_USERNAME'),
            'DatabasePassword' => env('DB_PASSWORD')
        ];
    }

    public static function connectDB($dbName): void
    {
        try {
            Config::set("database.connections.mysql_company.database", $dbName);
            Config::set("database.connections.mysql_company.host", env('DB_HOST'));
            Config::set("database.connections.mysql_company.username", env('DB_USERNAME'));
            Config::set("database.connections.mysql_company.password", env('DB_PASSWORD'));
            DB::purge('mysql_company');
            DB::connection('mysql_company')->getPdo();
            //Log::info('Connected to DB: ' . $dbName);
        } catch (Exception $exception) {
            Log::error('Error connecting to DB: ' . $exception->getMessage());
            //throw new Exception('DB not found', 400);
        }
    }

    public static function connectCloudSqlDB($company): void
    {
        try {
            $databaseHost = $company['DatabaseHost'];
            $databaseUser = $company['DatabaseUser'];
            $databasePassword = $company['DatabasePassword'];
            if (!empty($databasePassword)) {
                $encrypter = new Encrypter(env('DB_ENCRYPTION_KEY', ''), config('app.cipher'));
                $databasePassword = $encrypter->decrypt($databasePassword);
            }
            $databaseName = $company['DatabaseName'];

            if (!empty($databaseHost)) {
                if (!App::environment('production')) {
                    $databaseHost = env('DEV_GOOGLE_SQL_HOST');
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
            //Log::info("Connected to Cloud SQL: $databaseHost DB: $databaseName ");
        } catch (Exception $exception) {
            Log::error('Error connecting to Cloud SQL DB: ' . $exception->getMessage());
            //throw new Exception('DB not found', 400);
        }
    }


    public static function setDatabaseConnectionAndRunQueries($connection, $queries): void
    {
        CompanyService::setDatabaseConnection($connection);
        foreach ($queries as $sql) {
            try {
                DB::connection('mysql_company')->statement($sql);
                //Log::info("Query executed successfully. \nQuery: {$sql}");
            } catch (Exception $exception) {
                Log::error("Query execution failed. \nQuery: {$sql}\nMessage: {$exception->getMessage()}");
            }
        }
    }
}
