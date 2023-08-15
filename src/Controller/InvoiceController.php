<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Dompdf\Dompdf;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Ses\SesClient;

class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly InvoiceRepository $invoiceRepository
    ) {
    }

    #[Route('/invoices', methods: ['GET'])]
    public function index(): Response
    {
        $invoices = $this->invoiceRepository->findAll();

        $data = $this->serializer->serialize($invoices, 'json');

        return new JsonResponse($data, json: true);
    }

    #[Route('/invoices', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $invoice = new Invoice();
        $invoice->setClient($data['client']);
        $invoice->setAmount($data['amount']);

        $this->invoiceRepository->save($invoice, true);

        $data = $this->serializer->serialize($invoice, 'json');

        return new JsonResponse($data, json: true);
    }

    #[Route('/invoices/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $invoice = $this->invoiceRepository->find($id);
        $invoice->setClient($data['client']);
        $invoice->setAmount($data['amount']);

        $this->invoiceRepository->save($invoice, true);

        $data = $this->serializer->serialize($invoice, 'json');

        return new JsonResponse($data, json: true);
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
            'region'  => 'eu-west-1',
            'credentials' => [
                'secret' => 'pdqasavYF8s39no2MU9Qdnkky1HcAwwLWsENXTnG',
                'key' => 'AKIA4PXMZYYVI5BFHVLJ'
            ]
        ]);

        try {
            $response = $s3->putObject([
                'Bucket' => 'awsdeveloper-01',
                'Key'    => "invoice-{$invoice->getId()}.pdf",
                'Body'   => file_get_contents(stream_get_meta_data($temp)['uri']),
                'ACL'    => 'public-read',
            ]);

            return new JsonResponse($response->get('ObjectURL'));
        } catch (S3Exception $e) {
            return new JsonResponse("There was an error uploading the file.\n{$e->getMessage()}", 404);
        }
    }

    #[Route('/invoices/{id}/send-email', methods: ['POST'])]
    public function sendEmail(int $id, Request $request) {

    }
}