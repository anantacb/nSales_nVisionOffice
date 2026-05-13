<?php

namespace App\Repositories\Plugin\NvmGqlApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;

class NvmGqlApiRepository
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
        $this->baseUrl = env('NVM_GQL_API_URL');
    }


    /**
     * @return mixed
     */
    public function cacheClear(): mixed
    {
        // Define the GraphQL mutation
        $mutation = <<<'GRAPHQL'
                        mutation {
                          CacheClearMutation {
                            Message
                            Success
                          }
                        }
                        GRAPHQL;

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'developerAccessKey' => env('DEVELOPER_ACCESS_KEY'),
                ],
                'json' => [
                    'query' => $mutation
                ],
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            return [
                "success" => true,
                "data" => $response['data']['CacheClearMutation']
            ];
        } catch (Exception|GuzzleException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }
}
