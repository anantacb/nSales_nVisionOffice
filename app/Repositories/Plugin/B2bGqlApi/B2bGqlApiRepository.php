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

    public function createLoginToken($companyDomain, $userName, $password)
    {
        // Define the GraphQL mutation
        $mutation = <<<'GRAPHQL'
                        mutation($userName:String,$password:String){
                            createLoginToken(userName:$userName , password: $password) {
                                token
                            }
                        }
                        GRAPHQL;

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'companyDomain' => $companyDomain,
                    'application' => 'web'
                ],
                'json' => [
                    'query' => $mutation,
                    'variables' => [
                        "userName" => $userName,
                        "password" => $password
                    ]
                ],
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            return [
                "success" => true,
                "data" => $response['data']['createLoginToken']
            ];
        } catch (Exception|GuzzleException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    public function getItemgroups($authToken)
    {
        // Define the GraphQL mutation
        $mutation = <<<'GRAPHQL'
                        query{
                            Itemgroups{
                                Name
                                SystemKey
                            }
                        }
                        GRAPHQL;

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                    'application' => 'web'
                ],
                'json' => [
                    'query' => $mutation
                ],
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            return [
                "success" => true,
                "data" => $response['data']['Itemgroups']
            ];
        } catch (Exception|GuzzleException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    public function getItemgroupProducts($authToken, $number, $perPage)
    {
        // Define the GraphQL mutation
        $mutation = <<<'GRAPHQL'
                        query($number: String, $per_page: Int){
                            ItemgroupProducts(number: $number, per_page: $per_page){
                                data{
                                  Name1
                                  Number
                                  Price{
                                    Price
                                  }
                                  Media{
                                    FullPath
                                  }
                                }
                           }
                        }
                        GRAPHQL;

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                    'application' => 'web'
                ],
                'json' => [
                    'query' => $mutation,
                    'variables' => [
                        "number" => $number,
                        "per_page" => $perPage
                    ]
                ],
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            return [
                "success" => true,
                "data" => $response['data']['ItemgroupProducts']
            ];
        } catch (Exception|GuzzleException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }
}
