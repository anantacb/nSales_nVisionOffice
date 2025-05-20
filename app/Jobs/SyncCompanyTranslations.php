<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncCompanyTranslations implements ShouldQueue
{
    use Queueable;

    protected int $companyId;

    /**
     * Create a new job instance.
     */
    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Company Translation Sync Started: ' . $this->companyId);
        Artisan::call("script:company-translation:add-element-or-key", [
            "--companyId" => $this->companyId,
        ]);
    }
}
