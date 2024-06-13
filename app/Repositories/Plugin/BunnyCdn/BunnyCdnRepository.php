<?php

namespace App\Repositories\Plugin\BunnyCdn;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class BunnyCdnRepository
{
    protected Client $client;
    protected string $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = env('BUNNY_CDN_BASE_URL');
    }

    /**
     * @param $page
     * @param $perPage
     * @return array
     */
    public function getStorageZones($page, $perPage): array
    {
        try {
            $response = $this->client->get("$this->baseUrl/storagezone?page=$page&perPage=$perPage&includeDeleted=false", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'accept' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Get Storage Zones API Error. \nError: {$exception->getMessage()}");
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $name
     * @return array
     */
    public function addStorageZone($name): array
    {
        try {
            $response = $this->client->post("$this->baseUrl/storagezone", [
                'body' => json_encode(
                    [
                        "ZoneTier" => 0,
                        "Name" => $name,
                        "Region" => "DE"
                    ]
                ),
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "code" => $response->getStatusCode(),
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException|ClientException $exception) {
            Log::error("Bunny Add Storage Zones API Error. \nError: {$exception->getMessage()}");
            $message = $exception->getMessage();
            if ($exception instanceof ClientException) {
                $errorResponse = json_decode($exception->getResponse()->getBody()->getContents(), true);
                $message = $errorResponse["Message"];
            }
            return [
                "status" => false,
                "code" => $exception->getCode(),
                "message" => $message
            ];
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function getStorageZone($id): array
    {
        try {
            $response = $this->client->get("$this->baseUrl/storagezone/$id", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'accept' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Get Storage Zones API Error. \nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteStorageZone($id): array
    {
        try {
            $response = $this->client->delete("$this->baseUrl/storagezone/$id", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY')
                ],
            ]);
            return [
                "success" => true,
                "code" => $response->getStatusCode(),
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException|ClientException $exception) {
            Log::error("Bunny Delete Storage Zones API Error. \nError: {$exception->getMessage()}");
            $message = $exception->getMessage();
            if ($exception instanceof ClientException) {
                $errorResponse = json_decode($exception->getResponse()->getBody()->getContents(), true);
                $message = $errorResponse["Message"];
            }
            return [
                "status" => false,
                "code" => $exception->getCode(),
                "message" => $message
            ];
        }
    }

    /**
     * @param $path
     * @return array
     */
    public function delete($path): array
    {
        $selected_company = Session::get('selected_company');
        $rootPath = $selected_company->imageHostAccount->FTPRootPath ?: $selected_company->DomainName;
        $target_url = "https://{$selected_company->imageHostAccount->FTPDomainName}/$rootPath/$path";
        try {
            $response = $this->client->delete($target_url,
                [
                    'headers' => [
                        'AccessKey' => $selected_company->imageHostAccount->FTPPassword,
                        'content-type' => 'application/octet-stream',
                    ],
                ]
            );
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Delete Asset API Error. \nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $page
     * @param $perPage
     * @return array
     */
    public function getPullZones($page, $perPage): array
    {
        try {
            $response = $this->client->get("$this->baseUrl/pullzone?page=$page&perPage=$perPage&includeCertificate=false", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'accept' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Get Pull Zones API Error. \nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $name
     * @param $storageZoneId
     * @return array
     */
    public function addPullZone($name, $storageZoneId): array
    {
        try {
            $response = $this->client->post("$this->baseUrl/pullzone", [
                'body' => json_encode(
                    [
                        "Name" => $name,
                        "StorageZoneId" => $storageZoneId,
                    ]
                ),
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "code" => $response->getStatusCode(),
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException|ClientException $exception) {
            Log::error("Bunny Add Pull Zones API Error. \nError: {$exception->getMessage()}");
            $message = $exception->getMessage();
            if ($exception instanceof ClientException) {
                $errorResponse = json_decode($exception->getResponse()->getBody()->getContents(), true);
                $message = $errorResponse["Message"];
            }
            return [
                "status" => false,
                "code" => $exception->getCode(),
                "message" => $message
            ];
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function getPullZone($id): array
    {
        try {
            $response = $this->client->get("$this->baseUrl/pullzone/$id", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'accept' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Get Pull Zones API Error. \nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function deletePullZone($id): array
    {
        try {
            $response = $this->client->delete("$this->baseUrl/pullzone/$id", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY')
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Get Storage Zones API Error. \nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $url
     * @return array
     */
    public function purgeCache($url): array
    {
        try {
            $response = $this->client->post("$this->baseUrl/purge?url=$url&async=true", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Purge Cache API Error: \nURL: $url\nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $path
     * @param $file
     * @return array
     */
    public function upload($path, $file): array
    {
        $selected_company = Session::get('selected_company');
        $rootPath = $selected_company->imageHostAccount->FTPRootPath ?: $selected_company->DomainName;
        $target_url = "https://{$selected_company->imageHostAccount->FTPDomainName}/$rootPath/$path";
        try {
            $response = $this->client->put($target_url, [
                'body' => file_get_contents($file),
                'headers' => [
                    'AccessKey' => $selected_company->imageHostAccount->FTPPassword,
                    'content-type' => 'application/octet-stream',
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Upload API Error: \nTarget URL: $target_url\nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }


    }

    /**
     * @param $id
     * @return array
     */
    public function pullZonePurgeCache($id): array
    {
        try {
            $response = $this->client->post("$this->baseUrl/pullzone/$id/purgeCache", [
                'headers' => [
                    'AccessKey' => env('BUNNY_API_KEY'),
                    'content-type' => 'application/json',
                ],
            ]);
            return [
                "success" => true,
                "data" => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (Exception|GuzzleException $exception) {
            Log::error("Bunny Pull Zone Purge Cache API Error: \nError: {$exception->getMessage()}");
            return [
                "status" => false,
                "message" => $exception->getMessage()
            ];
        }
    }
}
