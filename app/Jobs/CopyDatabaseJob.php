<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class CopyDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dbType;
    protected $dbName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dbType, $dbName)
    {
        $this->dbType = $dbType;
        $this->dbName = $dbName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('db:copy-prod-to-dev', [
            '--dbType' => $this->dbType,
            '--dbName' => $this->dbName,
        ]);
    }
}
