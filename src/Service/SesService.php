<?php

namespace App\Service;

use Aws\Result;
use Aws\Ses\SesClient;

class SesService
{
    private SesClient $client;

    public function __construct(
        string                  $aws_region,
        string                  $aws_secret_key,
        string                  $aws_access_key,
    ) {
        $this->client = new SesClient([
            'version' => '2010-12-01',
            'region'  => $aws_region,
            'credentials' => [
                'secret' => $aws_secret_key,
                'key' => $aws_access_key,
            ]
        ]);
    }

    public function sendEmail(string $source, array $destination, array $message): Result
    {
        return $this->client->sendEmail([
            'Source' => $source,
            'Destination' => $destination,
            'Message' => $message,
        ]);
    }
}