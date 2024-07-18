<?php

namespace App\Override\Postmark;

use GuzzleHttp\Exception\GuzzleException;
use Postmark\Models\PostmarkException;
use Postmark\Models\PostmarkServer;
use Postmark\PostmarkAdminClient;

class OverriddenPostmarkAdminClient extends PostmarkAdminClient
{
    public function __construct(string $accountToken, int $timeout = 60)
    {
        parent::__construct($accountToken, $timeout);
    }

    /**
     * @param int $SourceServerID
     * @param int $DestinationServerID
     * @param bool $PerformChanges
     * @return PostmarkServer
     * @throws GuzzleException
     * @throws PostmarkException
     */
    public function pushTemplatesToAnotherServer(
        int  $SourceServerID,
        int  $DestinationServerID,
        bool $PerformChanges = true
    ): PostmarkServer
    {
        $body = [];
        $body['SourceServerID'] = $SourceServerID;
        $body['DestinationServerID'] = $DestinationServerID;
        $body['PerformChanges'] = $PerformChanges;
        return new PostmarkServer((array)$this->processRestRequest('PUT', '/templates/push', $body));
    }
}
