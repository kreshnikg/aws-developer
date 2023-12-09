<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Service\EmailService;
use App\Service\SqsService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\AwsException;
use Dompdf\Dompdf;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly SqsService $sqsService,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/invoices', methods: ['GET'])]
    public function index(): Response
    {
        $this->logger->error("Invoices");

        $invoices = $this->invoiceRepository->findAll();

        return $this->json($invoices);
    }

    #[Route('/invoices', methods: ['POST'])]
    public function save(Request $request): JsonResponse|array
    {
        $data = $request->toArray();

        $invoice = new Invoice();
        $invoice->setClient($data['client']);
        $invoice->setAmount($data['amount']);
        $this->invoiceRepository->save($invoice, true);

        $client = new DynamoDbClient([
            'version' => 'latest',
            'region'  => $this->getParameter('aws_region'),
            'credentials' => [
                'secret' => $this->getParameter('aws_secret_key'),
                'key' => $this->getParameter('aws_access_key')
            ]
        ]);
        foreach ($data['items'] as $item) {
            $client->putItem([
                'TableName' => 'awsdeveloper-invoice-items',
                'Item' => [
                    'InvoiceId' => ['N' => (string) $invoice->getId()],
                    'Title' => ['S' => (string) $item['title']],
                    'Price' => ['S' => (string) $item['price']],
                    'Quantity' => ['S' => (string) $item['quantity']],
                ]
            ]);
        }

        return $this->json($invoice);
    }

    #[Route('/invoices/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $invoice = $this->invoiceRepository->find($id);
        $invoice->setClient($data['client']);
        $invoice->setAmount($data['amount']);

        $this->invoiceRepository->save($invoice, true);

        return $this->json($invoice);
    }

    #[Route('/invoices/{id}/download', methods: ['GET'])]
    public function download(int $id, Request $request): JsonResponse {
        $invoice = $this->invoiceRepository->find($id);

        $dompdf = new Dompdf();
        $dompdf->loadHtml("<h1>Invoice {$invoice->getId()}</h1><br/><div>Client: {$invoice->getClient()}<br/>Amount: {$invoice->getAmount()}</div>");
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $temp = tmpfile();
        fwrite($temp, $dompdf->output());

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $this->getParameter('aws_region'),
            'credentials' => [
                'secret' => $this->getParameter('aws_secret_key'),
                'key' => $this->getParameter('aws_access_key')
            ]
        ]);

        try {
            $response = $s3->putObject([
                'Bucket' => 'awsdeveloper-invoices',
                'Key'    => "invoice-{$invoice->getId()}.pdf",
                'Body'   => file_get_contents(stream_get_meta_data($temp)['uri']),
                'ACL'    => 'public-read',
            ]);

            return $this->json($response->get('ObjectURL'));
        } catch (S3Exception $e) {
            return $this->json("There was an error uploading the file.\n{$e->getMessage()}", 404);
        }
    }

    #[Route('/invoices/{id}/send-email', methods: ['POST'])]
    public function sendEmail(int $id, Request $request): JsonResponse {
        $invoice = $this->invoiceRepository->find($id);

        $result = $this->emailService->sendInvoice($invoice);

        return $this->json($result);
    }

    #[Route('/invoices/{id}/send-email-async', methods: ['POST'])]
    public function sendEmailAsync(int $id, Request $request): JsonResponse {
        $invoice = $this->invoiceRepository->find($id);

        try {
            $result = $this->sqsService->sendMessage(json_encode(['invoiceId' => $invoice->getId()]));

            return $this->json($result);
        } catch (AwsException $e) {
            error_log($e->getMessage());
        }

        return $this->json("success");
    }

    #[Route('/invoices/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $invoice = $this->invoiceRepository->find($id);

        $this->invoiceRepository->remove($invoice, true);

        return $this->json("success");
    }
}