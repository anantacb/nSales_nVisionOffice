<?php

namespace App\Repositories\Plugin\Postmark;

use App\Override\Postmark\OverriddenPostmarkAdminClient as PostmarkAdminClient;
use GuzzleHttp\Exception\GuzzleException;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;

class PostmarkRepository
{
    private PostmarkClient|PostmarkAdminClient $client;

    public function __construct()
    {
        $this->client = new PostmarkClient(env('POSTMARK_SERVER_API_TOKEN'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function deleteServer(int $id): array
    {
        $this->setAdminClient();
        try {
            $response = $this->client->deleteServer($id);
            return [
                'success' => true,
                'data' => (array)$response
            ];
        } catch (PostmarkException $exception) {
            return [
                "success" => false,
                "code" => $exception->getCode(),
                "message" => $exception->getMessage()
            ];
        }
    }

    private function setAdminClient(): void
    {
        $this->client = new PostmarkAdminClient(env('POSTMARK_ACCOUNT_API_TOKEN'));
    }

    /**
     * @param int $count
     * @param int $offset
     * @param string $name
     * @return array
     */
    public function listServers(int $count, int $offset = 0, string $name = ''): array
    {
        $this->setAdminClient();
        try {
            $response = $this->client->listServers($count, $offset, $name);
            return [
                'success' => true,
                'data' => (array)$response
            ];
        } catch (PostmarkException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param int $sourceServerId
     * @param int $destinationServerId
     * @return array
     */
    public function pushTemplatesToAnotherServer(int $sourceServerId, int $destinationServerId): array
    {
        $this->setAdminClient();
        try {
            $response = $this->client->pushTemplatesToAnotherServer($sourceServerId, $destinationServerId);
            return [
                'success' => true,
                'data' => (array)$response
            ];
        } catch (PostmarkException|GuzzleException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    public function createSenderSignature(
        string $fromEmail,
        string $name,
        string $replyToEmail = null,
        string $returnPathDomain = null,
        string $confirmationPersonalNote = null
    ): array
    {
        $this->setAdminClient();
        try {
            $senderSignature = $this->client->createSenderSignature($fromEmail, $name, $replyToEmail, $returnPathDomain, $confirmationPersonalNote);
            return [
                'success' => true,
                'data' => (array)$senderSignature
            ];
        } catch (PostmarkException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param string $name
     * @param string $color Purple Blue Turquoise Green Red Yellow Grey Orange
     * @return array
     */
    public function createServer(string $name, string $color = "Purple"): array
    {
        $this->setAdminClient();
        try {
            $server = $this->client->createServer($name, $color);
            return [
                'success' => true,
                'data' => (array)$server
            ];
        } catch (PostmarkException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @param string|int $templateIdOrAlias
     * @param array $templateData
     * @param string|NULL $cc
     * @param string|NULL $bcc
     * @param string $postmarkToken
     * @return array
     */
    public function sendEmailWithTemplate(string $from, string $to, string|int $templateIdOrAlias, array $templateData, string $cc = NULL, string $bcc = NULL, string $postmarkToken = ""): array
    {
        $this->setServerClient($postmarkToken);
        try {
            $sendEmailResponse = $this->client->sendEmailWithTemplate(
                $from, $to,
                $templateIdOrAlias,
                $templateData,
                true, null, true, null,
                $cc, $bcc
            );
            return [
                'success' => true,
                'data' => (array)$sendEmailResponse
            ];
        } catch (PostmarkException $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }
    }

    public function setServerClient($postmarkToken): PostmarkRepository
    {
        $this->client = new PostmarkClient($postmarkToken);
        return $this;
    }
}
