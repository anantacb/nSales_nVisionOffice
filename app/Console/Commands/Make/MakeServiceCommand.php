<?php

namespace App\Console\Commands\Make;

use App\Services\Make\MakeServiceService;
use Exception;
use Illuminate\Console\Command;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Service Class and Service Repository Interface.';

    protected MakeServiceService $makeServiceService;

    public function __construct(MakeServiceService $makeServiceService)
    {
        parent::__construct();
        $this->makeServiceService = $makeServiceService;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $service = $this->argument('service');
        $paths = $this->makeServiceService->create($service);
        foreach ($paths as $path) {
            $this->info("File : {$path} created.");
        }
    }
}
