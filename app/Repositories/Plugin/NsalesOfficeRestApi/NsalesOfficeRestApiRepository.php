<?php

namespace App\Repositories\Plugin\NsalesOfficeRestApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;

class NsalesOfficeRestApiRepository
{
    protected Client $client;
    protected string $baseUrl;

    public function __construct()
    {
        $clientOptions = ['timeout' => 60, 'connect_timeout' => 5];
        if (!App::environment(['production', 'development'])) {
            $clientOptions['verify'] = false;
        }
        $this->client = new Client($clientOptions);
        $this->baseUrl = env('NSALES_OFFICE_APP_URL');
    }

    /**
     * @return mixed
     */
    public function cacheClear(): array
    {
        try {
            $response = $this->client->post("$this->baseUrl/api/v1.0/cache-clear", [
                'form_params' => [
                    'developerAccessKey' => env('DEVELOPER_ACCESS_KEY'),
                ]
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            return [
                "success" => true,
                "data" => $response
            ];
        } catch (Exception|GuzzleException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }
}
