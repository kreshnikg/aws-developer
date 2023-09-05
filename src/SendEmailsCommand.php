<?php

namespace App;

use App\Repository\InvoiceRepository;
use App\Service\EmailService;
use App\Service\SqsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailsCommand extends Command
{
    public function __construct(
        private readonly SqsService $sqsService,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly EmailService $emailService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:send-emails')
            ->setDescription('Send emails from SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $this->sqsService->pullMessage();
        if (!$message) {
            $output->write("No emails to send");

            return Command::SUCCESS;
        }

        $messageBody = json_decode(json: $message["Body"], associative: true);

        $invoice = $this->invoiceRepository->find($messageBody["invoiceId"]);

        $result = $this->emailService->sendInvoice($invoice);

        if ($result["@metadata"]["statusCode"] === 200) {
            $this->sqsService->deleteMessage($message["ReceiptHandle"]);
        }

        $output->write("Success sending invoice #{$invoice->getId()} to email");

        return Command::SUCCESS;
    }
}