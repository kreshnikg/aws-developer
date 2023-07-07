<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoices')]
    public function index(InvoiceRepository $invoiceRepository): JsonResponse
    {
        $invoice = new Invoice();
        $invoice->setClient('Klienti');
        $invoice->setAmount(100);

        $invoiceRepository->save($invoice, true);

        return new JsonResponse(['success. Invoice ID: ' . $invoice->getId()]);
    }


}