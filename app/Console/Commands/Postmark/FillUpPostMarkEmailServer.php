<?php

namespace App\Console\Commands\Postmark;

use App\Models\Office\Module;
use App\Models\Office\PostmarkEmailServer;
use App\Repositories\Plugin\Postmark\PostmarkRepository;
use Illuminate\Console\Command;

class FillUpPostMarkEmailServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postmark:fill-up-postmark-email-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch servers from postmark and match them with our companies and store in database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $companyWithModuleSettingValue = [];
        $module = Module::with([
            'moduleSettings' => function ($q) {
                $q->with(['settings' => function ($q) {
                    $q->with(['company']);
                }])->where('Name', 'PostmarkServerApiToken');
            }])
            ->whereIn('Name', ['System'])
            ->first();

        foreach ($module->moduleSettings[0]->settings as $setting) {
            $companyWithModuleSettingValue[$setting->Value] = [
                'CompanyId' => $setting->CompanyId,
                'CompanyName' => $setting->company->Name,
                'Value' => $setting->Value
            ];
        }

        $repository = new PostmarkRepository();
        $count = 100;
        $offset = 0;
        $continue = true;
        $servers = [];
        while ($continue) {
            $response = $repository->listServers($count, $offset);
            $offset += $count;
            $servers = array_merge($servers, $response['data']['Servers']);
            if ($offset >= $response['data']['TotalCount']) {
                $continue = false;
            }
        }

        PostmarkEmailServer::truncate();

        foreach ($servers as $server) {
            PostmarkEmailServer::create([
                'ServerId' => $server->ID,
                'ServerName' => $server->Name,
                'ServerDetails' => $server,
                'CompanyId' => $this->getCompanyId($companyWithModuleSettingValue, $server->ApiTokens),
                'ApiToken' => $server->ApiTokens[0]
            ]);
        }
        $this->info("Success");
        return Command::SUCCESS;
    }

    private function getCompanyId($companyWithModuleSettingValue, $apiTokens)
    {
        foreach ($apiTokens as $apiToken) {
            if (isset($companyWithModuleSettingValue[$apiToken])) {
                return $companyWithModuleSettingValue[$apiToken]['CompanyId'];
            }
        }
        return null;
    }
}
