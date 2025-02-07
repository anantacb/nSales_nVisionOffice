<?php

namespace App\Repositories\Plugin\NsalesAdminDjangoApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class NsalesAdminDjangoApiRepository
{
    protected Client $client;
    protected string $baseUrl;
    protected string $username;
    protected string $password;

    public function __construct()
    {
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
     * @throws GuzzleException|ClientException|ServerException|ConnectException|Exception
     */
    public function getResponseFromAPI(string $apiEndPoint, array $headers, array $params, string $method): array
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
                'status_code' => $response->getStatusCode(),
                'data' => json_decode($data, true)
            ];
        } catch (ClientException $exception) {
            // Catch 400 level errors (Bad Request, Unauthorized, Forbidden, etc.)
            $response = $exception->getResponse();
            $statusCode = $response->getStatusCode();
            $errorData = json_decode($response->getBody()->getContents(), true);
            $this->writeErrorLog($url, $statusCode, $exception, 'ConnectException occurred in getResponseFromAPI');

            return [
                'success' => false,
                'status_code' => $statusCode,
                'message' => 'Client error occurred.',
                'errors' => $errorData
            ];
        } catch (ServerException $exception) {
            // Catch 500 level errors (Internal Server Error, Bad Gateway, etc.)
            $response = $exception->getResponse();
            $statusCode = $response->getStatusCode();
            $this->writeErrorLog($url, $statusCode, $exception, 'ServerException occurred in getResponseFromAPI');

            return [
                'success' => false,
                'status_code' => $statusCode,
                'message' => 'Server error occurred. Please try again later.'
            ];
        } catch (ConnectException $exception) {
            // Handle network errors (Timeout, DNS failure, etc.)
            $this->writeErrorLog($url, 503, $exception, 'ConnectException occurred in getResponseFromAPI');

            return [
                'success' => false,
                'status_code' => 503,
                'message' => 'Network error. Unable to connect to the server.'
            ];
        } catch (Exception $exception) {
            // Catch any other exceptions
            $this->writeErrorLog($url, 500, $exception, 'Exception occurred in getResponseFromAPI');

            return [
                'success' => false,
                'status_code' => 500,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function writeErrorLog($url, $statusCode, $exception, $logMessage): void
    {
        // Log the error
        Log::error($logMessage, [
            'url' => $url,
            'status_code' => $statusCode,
            'exception_message' => $exception->getMessage(),
        ]);
    }

    /**
     * @param string $companyDomain
     * @param string $prodStatus
     * @param string $devStatus
     * @return array
     * @throws GuzzleException|ClientException|ServerException|ConnectException|Exception
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

    /**
     * @param string $customDomain
     * @param string $uuid
     * @return array
     * @throws GuzzleException|ClientException|ServerException|ConnectException|Exception
     */
    public function addCompanyCustomDomain(string $customDomain, string $uuid): array
    {
        $apiEndPoint = "/b2b/hosts/";
        $basicAuthToken = base64_encode("{$this->username}:{$this->password}");
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Basic {$basicAuthToken}",
        ];
        $params = [
            "hostname" => $customDomain,
            "generate_certificate" => true,
            "active" => true,
            "deployment" => $uuid
        ];

        return $this->getResponseFromAPI($apiEndPoint, $headers, $params, 'POST');
    }

    /**
     * @param string $hostId
     * @return array
     * @throws GuzzleException|ClientException|ServerException|ConnectException|Exception
     */
    public function deleteCompanyCustomDomain(string $hostId): array
    {
        $apiEndPoint = "/b2b/hosts/$hostId/";
        $basicAuthToken = base64_encode("{$this->username}:{$this->password}");
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Basic {$basicAuthToken}",
        ];

        return $this->getResponseFromAPI($apiEndPoint, $headers, [], 'DELETE');
    }


}
