<?php

namespace App;

use App\Repository\InvoiceRepository;
use App\Service\EmailService;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use Bref\Event\Sqs\SqsHandler;
use Bref\Event\Sqs\SqsRecord;
use Psr\Log\LoggerInterface;

class WorkerHandler extends SqsHandler
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function processRecord(SqsRecord $record)
    {
        $message = json_decode(
            json: $record->getBody(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        $invoice = $this->invoiceRepository->find($message["invoiceId"]);

        $this->logger->info("Sending invoice #{$invoice->getId()} to email");

        $result = $this->emailService->sendInvoice($invoice);

        if ($result["@metadata"]["statusCode"] !== 200) {
            throw new \RuntimeException("Error sending email, messageId: {$record->getMessageId()}, Body: {$record->getBody()}");
        }

        $this->logger->info("Success sending invoice #{$invoice->getId()} to email");
    }

    public function handleSqs(SqsEvent $event, Context $context): void
    {
        foreach ($event->getRecords() as $record) {
            try {
                $this->processRecord($record);
            } catch (\Throwable $th) {
                echo $th->getMessage();

                $this->markAsFailed($record);
            }
        }
    }
}