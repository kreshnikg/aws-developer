<?php

namespace App;

use App\Repository\InvoiceRepository;
use App\Service\EmailService;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use Bref\Event\Sqs\SqsHandler;
use Bref\Event\Sqs\SqsRecord;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

class WorkerHandler extends SqsHandler
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger,
        private readonly string $env,
    ) {
        echo $this->env;
    }

    /**
     * @throws \JsonException
     */
    public function processRecord(SqsRecord $record)
    {
        echo "Environment: {$this->env}\n";

        $message = json_decode(
            json: $record->getBody(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        $this->logger->info("Processing message #{$record->getMessageId()}, Body: {$record->getBody()}");

        $invoice = $this->invoiceRepository->find($message["invoiceId"]);

        $this->logger->info("Sending invoice #{$invoice->getId()} to email");

        $result = $this->emailService->sendInvoice($invoice);

        if ($result["@metadata"]["statusCode"] !== 200) {
            $this->logger->error("Error sending email, messageId: {$record->getMessageId()}, Body: {$record->getBody()}");

            throw new \RuntimeException("Error sending email, messageId: {$record->getMessageId()}, Body: {$record->getBody()}");
        }

        $this->logger->info("Success sending invoice #{$invoice->getId()} to email");
    }

    public function handleSqs(SqsEvent $event, Context $context): void
    {
        $this->logger->info("Fetching messages from SQS");

        foreach ($event->getRecords() as $record) {
            try {
                $this->processRecord($record);
            } catch (\Throwable $th) {
                $this->logger->error($th->getMessage());

                $this->markAsFailed($record);
            }
        }
    }
}