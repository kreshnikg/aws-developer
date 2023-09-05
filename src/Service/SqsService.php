<?php

namespace App\Service;

use Aws\Result;
use Aws\Sqs\SqsClient;

class SqsService
{
    private SqsClient $client;

    public function __construct(
        string                  $aws_region,
        string                  $aws_secret_key,
        string                  $aws_access_key,
        private readonly string $sqs_email_queue_url,
    ) {
        $this->client = new SqsClient([
            'version' => '2012-11-05',
            'region'  => $aws_region,
            'credentials' => [
                'secret' => $aws_secret_key,
                'key' => $aws_access_key,
            ]
        ]);
    }

    public function sendMessage(string $body): Result {
        $params = [
            'DelaySeconds' => 10,
            'MessageBody' => $body,
            'QueueUrl' => $this->sqs_email_queue_url,
        ];

        return $this->client->sendMessage($params);
    }

    public function pullMessage(): array|null
    {
        $result = $this->client->receiveMessage([
            'MaxNumberOfMessages' => 1,
            'QueueUrl' => $this->sqs_email_queue_url,
        ]);

        if (!$result->get('Messages')) {
            return null;
        }

        return $result->get('Messages')[0];
    }

    public function deleteMessage(string $receiptHandle): void
    {
        $this->client->deleteMessage([
            'QueueUrl' => $this->sqs_email_queue_url,
            'ReceiptHandle' => $receiptHandle,
        ]);
    }
}