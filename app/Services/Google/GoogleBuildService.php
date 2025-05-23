<?php

namespace App\Services\Google;

use App\Contracts\ServiceDto;
use Exception;
use GuzzleHttp\Client;

class GoogleBuildService implements GoogleBuildServiceInterface
{

    public function triggerBuild($companyDomain): ServiceDto
    {
        try {
            $projectId = env("GOOGLE_CLOUD_BUILD_PROJECT_ID");
            $region = env("GOOGLE_CLOUD_BUILD_REGION");
            $apiKey = env("GOOGLE_CLOUD_BUILD_API_KEY");
            $apiSecret = env("GOOGLE_CLOUD_BUILD_API_SECRET");
            $webhookType = env("APP_ENV") === "production" ? "prod" : "dev";
            $triggerName = "webhook-$webhookType-b2bshop-$companyDomain";

            $request = "https://cloudbuild.googleapis.com/v1/projects/$projectId/locations/$region/triggers/$triggerName:webhook?key=$apiKey&secret=$apiSecret&trigger=$triggerName&projectId=$projectId";

            $client = new Client();
            $client->post($request, [
                'headers' => ['content-type' => 'application/json']
            ]);

            return new ServiceDto("Build triggered!", 201);
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
