<?php

namespace App\Console\Commands\Postmark;

use App\Models\Office\PostmarkEmailServer;
use App\Models\Office\PostmarkEmailServerTemplates;
use App\Repositories\Plugin\Postmark\PostmarkRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;

class FillUpPostMarkEmailServerTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postmark:fill-up-postmark-email-server-templates';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch server templates from postmark and match them with our companies and store in database.';
    private $encryptionKey;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->encryptionKey = env("POSTMARK_SERVER_API_TOKEN_ENCRYPTION_KEY");
        PostmarkEmailServerTemplates::truncate();
        $postmarkEmailServers = PostmarkEmailServer::all();

        $fetchedServerIds = [];
        $progressBar = $this->output->createProgressBar(count($postmarkEmailServers));
        $progressBar->start();

        $repository = new PostmarkRepository();
        foreach ($postmarkEmailServers as $postmarkEmailServer) {
            if (in_array($postmarkEmailServer['ServerId'], $fetchedServerIds)) {
                continue;
            }
            $postmarkEmailServerTemplates = [];
            $count = 100;
            $offset = 0;
            $continue = true;
            while ($continue) {
                $response = $repository->listTemplates($this->getDecryptedApiToken($postmarkEmailServer['EncryptedApiToken']), $count, $offset);
                $offset += $count;
                $templates = $response['data']['Templates'];
                foreach ($templates as $template) {
                    $templateDetails = $repository->getTemplate($this->getDecryptedApiToken($postmarkEmailServer['EncryptedApiToken']), $template['TemplateId']);
                    $postmarkEmailServerTemplates[] = [
                        'InsertTime' => Carbon::now(),
                        'UpdateTime' => Carbon::now(),
                        'ServerId' => $postmarkEmailServer['ServerId'],
                        'TemplateId' => $templateDetails['data']['TemplateId'],
                        'TemplateDetails' => json_encode($templateDetails['data']),
                    ];
                }
                if ($offset >= $response['data']['TotalCount']) {
                    $continue = false;
                }
            }
            PostmarkEmailServerTemplates::insert($postmarkEmailServerTemplates);
            $fetchedServerIds[] = $postmarkEmailServer['ServerId'];
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->info("Success");
        return Command::SUCCESS;
    }

    private function getDecryptedApiToken($apiToken)
    {
        return (new Encrypter($this->encryptionKey, config('app.cipher')))->decrypt($apiToken);
    }
}
