<?php

namespace App\Tests;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InvoiceControllerTest extends WebTestCase
{
    private InvoiceRepository $invoiceRepository;
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->invoiceRepository = static::getContainer()->get(InvoiceRepository::class);
    }

    public function testGetInvoices(): void
    {
        $invoice = new Invoice();
        $invoice->setAmount(99.99);
        $invoice->setClient('Test');
        $this->invoiceRepository->save($invoice, true);

        $this->client->xmlHttpRequest('GET', '/invoices');

        $response = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
        );

        $this->assertResponseIsSuccessful();

        $this->assertCount(1, $response);
        $this->assertEquals(99.99, $response[0]['amount']);
        $this->assertEquals('Test', $response[0]['client']);
    }

    public function testCreateInvoice(): void
    {
        $this->client->xmlHttpRequest('POST', '/invoices', [], [], [], json_encode([
            'client' => 'Create Invoice',
            'amount' => 89.99
        ]));

        $response = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
        );

        $invoice = $this->invoiceRepository->find($response['id']);
        $this->assertNotNull($invoice);
        $this->assertEquals('Create Invoice', $invoice->getClient());
        $this->assertEquals(89.99, $invoice->getAmount());
    }

    public function testUpdateInvoice(): void
    {
        $invoice = new Invoice();
        $invoice->setAmount(79.99);
        $invoice->setClient('Invoice to update');
        $this->invoiceRepository->save($invoice, true);

        $this->client->xmlHttpRequest('PUT', '/invoices/'.$invoice->getId(), [], [], [], json_encode([
            'client' => 'Updated Invoice',
            'amount' => 69.99
        ]));

        $this->assertResponseIsSuccessful();

        $response = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
        );

        $updated_invoice = $this->invoiceRepository->find($invoice->getId());
        $this->assertEquals('Updated Invoice', $updated_invoice->getClient());
        $this->assertEquals(69.99, $updated_invoice->getAmount());
    }
}
