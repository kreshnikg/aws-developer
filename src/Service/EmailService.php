<?php

namespace App\Service;

use App\Entity\Invoice;
use Aws\Result;

class EmailService
{
    public function __construct(
        private readonly SesService $sesService,
    ) {
    }

    public function sendInvoice(Invoice $invoice): Result {
        $message = [
            'Subject' => [
                'Data' => "Invoice #{$invoice->getId()}",
                'Charset' => 'UTF-8'
            ],
            'Body' => [
                'Html' => [
                    'Data' => "<h1>Invoice {$invoice->getId()}</h1><br/><div>Client: {$invoice->getClient()}<br/>Amount: {$invoice->getAmount()}</div>",
                    'Charset' => 'UTF-8'
                ]
            ]
        ];

        $destination = [
            'ToAddresses' => [
                'kreshnikg3@gmail.com'
            ]
        ];

        echo "Message: " . json_encode($message) . "\n";
        echo "Destination: " . json_encode($destination) . "\n";

        return $this->sesService->sendEmail(
            'kreshnikg3@gmail.com',
            $destination,
            $message
        );
    }
}