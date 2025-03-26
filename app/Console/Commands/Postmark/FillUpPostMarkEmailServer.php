<?php

namespace App\Console\Commands\Postmark;

use App\Models\Office\Module;
use App\Models\Office\PostmarkEmailServer;
use App\Repositories\Plugin\Postmark\PostmarkRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;

class FillUpPostMarkEmailServer extends Command
{
    public $companyWithModuleSettingValue = [];
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
    private $encryptionKey;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setCompanyModuleSettingValue();

        $this->encryptionKey = env("POSTMARK_SERVER_API_TOKEN_ENCRYPTION_KEY");

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

        $serversToInsert = [];

        collect($servers)->each(function ($server) use (&$serversToInsert) {
            $companyIds = $this->getCompanyIds($server['ApiTokens']);
            if ($companyIds) {
                foreach ($companyIds as $companyId) {
                    $serversToInsert[] = [
                        'InsertTime' => Carbon::now(),
                        'UpdateTime' => Carbon::now(),
                        'ServerId' => $server['ID'],
                        'ServerName' => $server['Name'],
                        'ServerDetails' => json_encode($server),
                        'CompanyId' => $companyId,
                        'EncryptedApiToken' => (new Encrypter($this->encryptionKey, config('app.cipher')))->encrypt($server['ApiTokens'][0]),
                    ];
                }
            } else {
                $serversToInsert[] = [
                    'InsertTime' => Carbon::now(),
                    'UpdateTime' => Carbon::now(),
                    'ServerId' => $server['ID'],
                    'ServerName' => $server['Name'],
                    'ServerDetails' => json_encode($server),
                    'CompanyId' => null,
                    'EncryptedApiToken' => (new Encrypter($this->encryptionKey, config('app.cipher')))->encrypt($server['ApiTokens'][0]),
                ];
            }
        })->toArray();

        PostmarkEmailServer::insert($serversToInsert);

        $this->info("Success");

        return Command::SUCCESS;
    }

    private function setCompanyModuleSettingValue()
    {
        $module = Module::with([
            'moduleSettings' => function ($q) {
                $q->with(['settings' => function ($q) {
                    $q->with(['company']);
                }])->where('Name', 'PostmarkServerApiToken');
            }])
            ->whereIn('Name', ['System'])
            ->first();

        foreach ($module->moduleSettings[0]->settings as $setting) {
            $this->companyWithModuleSettingValue[$setting->Value][] = [
                'CompanyId' => $setting->CompanyId,
                'CompanyName' => $setting->company->Name,
                'Value' => $setting->Value
            ];
        }
    }

    private function getCompanyIds($apiTokens)
    {
        $companyIds = [];
        foreach ($apiTokens as $apiToken) {
            if (isset($this->companyWithModuleSettingValue[$apiToken])) {
                $merging = collect($this->companyWithModuleSettingValue[$apiToken])->pluck('CompanyId')->toArray();
                $companyIds = array_merge($companyIds, $merging);
            }
        }
        return $companyIds;
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
