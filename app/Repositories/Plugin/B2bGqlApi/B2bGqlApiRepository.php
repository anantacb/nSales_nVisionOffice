<?php

namespace App\Repositories\Plugin\B2bGqlApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class B2bGqlApiRepository
{
    protected Client $client;
    protected string $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = env('B2B_GQL_API_URL');
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
                    'developerAccessKey' => env('B2B_GQL_API_DEVELOPER_ACCESS_KEY'),
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
