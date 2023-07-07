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

class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/invoices', methods: ['GET'])]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        $invoices = $invoiceRepository->findAll();

        $data = $this->serializer->serialize($invoices, 'json');

        return new JsonResponse($data, json: true);
    }

    #[Route('/invoices', methods: ['POST'])]
    public function save(Request $request, InvoiceRepository $invoiceRepository): JsonResponse
    {
        $data = $request->toArray();

        $invoice = new Invoice();
        $invoice->setClient($data['client']);
        $invoice->setAmount($data['amount']);

        $invoiceRepository->save($invoice, true);

        $data = $this->serializer->serialize($invoice, 'json');

        return new JsonResponse($data, json: true);
    }
}