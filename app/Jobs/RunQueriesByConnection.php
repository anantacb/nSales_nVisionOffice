<?php

namespace App\Jobs;

use App\Services\Company\CompanyService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RunQueriesByConnection implements ShouldQueue
{
    use Queueable;

    protected array $queriesWithConnections;

    /**
     * Create a new job instance.
     */
    public function __construct($queriesWithConnections)
    {
        $this->queue = 'query-execution';
        $this->queriesWithConnections = $queriesWithConnections;
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(): void
    {
        /*$connectionAndQueries = collect($this->queriesWithConnections)->groupBy('connection.DatabaseHost')->map(function ($sqlQueriesWithConnections) {
            $queries = [];
            $connection = [];
            foreach ($sqlQueriesWithConnections as $sqlQueriesWithConnection) {
                $connection = $sqlQueriesWithConnection['connection'];
                $queries = array_merge($queries, $sqlQueriesWithConnection['queries']);
            }
            return [
                'connection' => $connection,
                'queries' => $queries
            ];
        })->values()->toArray();

        foreach ($connectionAndQueries as $connectionAndQuery) {
            CompanyService::setDatabaseConnection($connectionAndQuery['connection']);
            foreach ($connectionAndQuery['queries'] as $sql) {
                try {
                    DB::connection('mysql_company')->statement($sql);
                } catch (Exception $exception) {
                    Log::error("Query execution failed. \nQuery: $sql \nMessage: {$exception->getMessage()}");
                }
            }
        }*/

        foreach ($this->queriesWithConnections as $queriesWithConnection) {
            CompanyService::setDatabaseConnection($queriesWithConnection['connection']);
            foreach ($queriesWithConnection['queries'] as $sql) {
                try {
                    DB::connection('mysql_company')->statement($sql);
                    Log::info("Query executed successfully. \nQuery: {$sql}");
                } catch (Exception $exception) {
                    Log::error("Query execution failed. \nQuery: {$sql}\nMessage: {$exception->getMessage()}");
                }
            }
        }
    }
}
