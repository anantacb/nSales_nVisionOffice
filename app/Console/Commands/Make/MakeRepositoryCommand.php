<?php

namespace App\Console\Commands\Make;

use App\Services\Make\MakeRepositoryService;
use Exception;
use Illuminate\Console\Command;

class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {--model=} {--type=office}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Repository And Repository Interface class for model.';

    protected MakeRepositoryService $makeRepositoryService;

    public function __construct(MakeRepositoryService $makeRepositoryService)
    {
        parent::__construct();
        $this->makeRepositoryService = $makeRepositoryService;
    }

    /**
     * Execute the console command.
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $model = $this->option('model');
        $type = $this->option('type');
        $paths = $this->makeRepositoryService->create($model, $type);
        foreach ($paths as $path) {
            $this->info("File : $path created.");
        }
    }
}
