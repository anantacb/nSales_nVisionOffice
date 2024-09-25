<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TypeError;

class CopyDBsProdToDev extends Command
{

    /**
     * The name and signature of the console command.
     * dbType in office|company
     * dbName
     * @var string
     */
    protected $signature = 'db:copy-prod-to-dev {--T|dbType=} {--N|dbName=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy database from one connection to another';

    protected string $toConnection;
    protected string $fromConnection;
    protected string $dbName;

    protected array $invalidData = [];

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        $this->toConnection = 'mysql_dev';

        $dbType = $this->option('dbType');
        $this->dbName = $this->option('dbName');

        switch (strtolower($dbType)) {
            case 'company':
                $this->fromConnection = 'mysql_company';
                Config::set('database.connections.mysql_company.database', $this->dbName);
                DB::reconnect($this->fromConnection);
                break;
            default:
                $this->fromConnection = 'mysql';
                break;
        }

        $this->info("Copying database '$this->dbName' from '$this->fromConnection' to '$this->toConnection'...");

        // Drop DB in destination and Create a new DB with same name
        $this->dropDbAndCreateDbInDestination();

        // Save the original sql_mode
        $originalSqlMode = DB::connection($this->toConnection)
            ->select("SELECT @@SESSION.sql_mode as sql_mode")[0]->sql_mode;

        // Disable strict SQL mode
        DB::connection($this->toConnection)->statement("SET SESSION SQL_MODE='NO_ENGINE_SUBSTITUTION';");

        // Get all tables from the source database
        $tables = $this->getSourceTables();

        foreach ($tables as $table) {
            $this->info("Creating Table and Copy Data: $table");

            // Disable foreign key checks on the destination connection
            DB::connection($this->toConnection)->statement('SET FOREIGN_KEY_CHECKS=0');

            $this->createTableInDestination($table);
            $this->insertDataInDestination($table);

            DB::connection($this->toConnection)->statement('SET FOREIGN_KEY_CHECKS=1');
            DB::connection($this->toConnection)->statement("SET SESSION sql_mode = '$originalSqlMode'");
        }

        $this->info('Database copy completed successfully!');

        Log::debug(print_r($this->invalidData, true));
    }

    private function dropDbAndCreateDbInDestination(): void
    {
        $dropQuery = "DROP DATABASE IF EXISTS `$this->dbName`;";
        $createQuery = "CREATE DATABASE `$this->dbName`;";
        DB::connection($this->toConnection)->statement($dropQuery);
        DB::connection($this->toConnection)->statement($createQuery);

        Config::set('database.connections.mysql_dev.database', $this->dbName);
        DB::reconnect($this->toConnection);
    }

    private function getSourceTables(): array
    {
        $database = DB::connection($this->fromConnection)->getDatabaseName();
        $tables = DB::connection($this->fromConnection)->select("SHOW TABLES");
        return collect($tables)->pluck("Tables_in_$database")->toArray();
    }

    private function createTableInDestination($table): void
    {
        // Get the creation table SQL from the source database
        $createTableSQL = DB::connection($this->fromConnection)
            ->select("SHOW CREATE TABLE `$table`");
        $createTableSQL = $createTableSQL[0]->{'Create Table'};

        // Create the table on the destination database
        DB::connection($this->toConnection)->statement($createTableSQL);
        $this->info("'$table': created on destination.");
    }

    private function insertDataInDestination($table): void
    {
        $skipTables = ['ScriptScheduleLogs'];
        if (in_array($table, $skipTables)) {
            $this->info("'$table': Data insert skipped on destination.");
            return;
        }
        $chunkSize = 200;
        $orderBy = DB::raw('1');
        if (in_array(strtolower($table), ['orderhead', 'orderline'])) {
            $orderBy = 'UUID';
        }

        DB::connection($this->fromConnection)->table($table)->orderBy($orderBy)->chunk($chunkSize, function ($prodData) use ($table) {
            $prodData = $prodData->map(function ($row) use ($table) {
                foreach ($row as $column => $value) {
                    if ($value == '0000-00-00 00:00:00') {
                        $this->invalidData[$this->dbName][$table][$column] = $value;
                        //Log::debug("DATABASE: $this->dbName TABLE: $table, COLUMN: $column Value: $value");
                        $row->$column = null;
                    }
                    if ($table == 'Company' && $column == 'GraphQLServiceURL') {
                        $row->$column = 'https://napi.dev.nsales.io/graphql';
                    }
                }
                return (array)$row;
            })->toArray();

            try {
                DB::connection($this->toConnection)->table($table)->insert($prodData);
            } catch (Exception|TypeError $exception) {
                Log::error($exception->getMessage());
            }
        });

        $this->info("'$table': Data inserted on destination.");
    }
}
