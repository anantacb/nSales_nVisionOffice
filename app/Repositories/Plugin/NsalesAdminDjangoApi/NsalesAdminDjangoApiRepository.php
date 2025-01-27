<?php

namespace App\Repositories\Plugin\NsalesAdminDjangoApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class NsalesAdminDjangoApiRepository
{
    protected Client $client;
    protected string $baseUrl;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        //$this->client = new Client(['verify' => false]);
        $this->client = new Client();
        $this->baseUrl = env('DJANGO_API_URL');
        $this->username = env('DJANGO_API_USERNAME');
        $this->password = env('DJANGO_API_PASSWORD');
    }

    /**
     * @param string $companyDomain
     * @return array
     */
    public function getCompanyDeploymentStatus(string $companyDomain): array
    {
        $apiEndPoint = "/b2b/deployment_api/" . $companyDomain;
        $basicAuthToken = base64_encode("{$this->username}:{$this->password}");
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Basic {$basicAuthToken}",
        ];

        return $this->getResponseFromAPI($apiEndPoint, $headers, [], 'GET');
    }

    /**
     * @param string $apiEndPoint
     * @param array $headers
     * @param array $params
     * @param string $method
     * @return array
     */
    public function getResponseFromAPI(string $apiEndPoint, array $headers, array $params, string $method = 'POST'): array
    {
        $url = $this->baseUrl . $apiEndPoint;

        try {
            $response = $this->client->request($method, $url, [
                "form_params" => $params,
                'headers' => $headers
            ]);
            $data = $response->getBody()->getContents();

            return [
                'success' => true,
                'data' => json_decode($data, true)
            ];
        } catch (GuzzleException|Exception $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param string $companyDomain
     * @param string $prodStatus
     * @param string $devStatus
     * @return array
     */
    public function startCompanyDeployment(string $companyDomain, string $prodStatus, string $devStatus): array
    {
        $apiEndPoint = "/b2b/deployment_api/";
        $basicAuthToken = base64_encode("{$this->username}:{$this->password}");
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Basic {$basicAuthToken}",
        ];
        $params = [
            "company_domain" => $companyDomain,
            "prod" => $prodStatus,
            "dev" => $devStatus
        ];

        return $this->getResponseFromAPI($apiEndPoint, $headers, $params, 'POST');
    }

}
