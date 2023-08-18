<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Ses\SesClient;

class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository
    ) {
    }

    #[Route('/invoices', methods: ['GET'])]
    public function index(): Response
    {
        $invoices = $this->invoiceRepository->findAll();

        return $this->json($invoices);
    }

    #[Route('/invoices', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $invoice = new Invoice();
        $invoice->setClient($data['client']);
        $invoice->setAmount($data['amount']);

        $this->invoiceRepository->save($invoice, true);

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
                'Bucket' => 'awsdeveloper-01',
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

        $sesClient = new SesClient([
            'version' => '2010-12-01',
            'region'  => $this->getParameter('aws_region'),
            'credentials' => [
                'secret' => $this->getParameter('aws_secret_key'),
                'key' => $this->getParameter('aws_access_key')
            ]
        ]);

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

        $result = $sesClient->sendEmail([
            'Source' => 'kreshnikg3@gmail.com',
            'Destination' => $destination,
            'Message' => $message,
        ]);

        return $this->json($result);
    }
}