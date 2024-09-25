<?php

namespace App\Console\Commands\Postmark;

use App\Models\Office\PostmarkEmailServer;
use App\Repositories\Plugin\Postmark\PostmarkRepository;
use Illuminate\Console\Command;

class PushNewTemplateToAllServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postmark:push-new-template-to-all-servers {--sourceServerId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push New Template to All Servers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourceServerId = $this->option('sourceServerId');
        $postmarkServersToCopy = PostmarkEmailServer::select(['ServerName', 'ServerId', 'CompanyId'])
            ->whereNotNull('CompanyId')
            ->get();

        $postmarkRepository = new PostmarkRepository();

        $bar = $this->output->createProgressBar($postmarkServersToCopy->count());
        $bar->start();
        foreach ($postmarkServersToCopy as $server) {
            $postmarkRepository->pushTemplatesToAnotherServer($sourceServerId, $server->ServerId);
            $bar->advance();
        }

        $bar->finish();

        $this->info("Success");
        return Command::SUCCESS;
    }
}
