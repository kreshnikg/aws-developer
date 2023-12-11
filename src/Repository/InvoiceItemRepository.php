<?php

namespace App\Repository;

use App\Entity\Invoice;
use Aws\DynamoDb\DynamoDbClient;

class InvoiceItemRepository
{
    private DynamoDbClient $dynamoDbClient;

    public function __construct(
        string                  $aws_region,
        string                  $aws_secret_key,
        string                  $aws_access_key,
    ) {
        $this->dynamoDbClient = new DynamoDbClient([
            'version' => 'latest',
            'region'  => $aws_region,
            'credentials' => [
                'secret' => $aws_secret_key,
                'key' => $aws_access_key
            ]
        ]);
    }

    public function getItemsByInvoiceId(int $invoiceId): array
    {
        $result = $this->dynamoDbClient->query([
            'TableName' => 'awsdeveloper-invoice-items',
            'KeyConditionExpression' => 'InvoiceId = :v1',
            'ExpressionAttributeValues' => [
                ':v1' => ['N' => (string) $invoiceId]
            ]
        ]);

        return array_map(function($item) {
            return [
                'title' => $item['Title']['S'],
                'price' => $item['Price']['S'],
                'quantity' => $item['Quantity']['S'],
            ];
        }, $result->toArray()['Items']);
    }

    public function deleteItemsWithInvoiceId(array $items, int $invoiceId): void
    {
        $this->dynamoDbClient->batchWriteItem([
            'RequestItems' => [
                'awsdeveloper-invoice-items' => array_map(function($item) use ($invoiceId) {
                    return [
                        'DeleteRequest' => [
                            'Key' => [
                                'InvoiceId' => ['N' => (string) $invoiceId],
                                'Title' => ['S' => (string) $item['title']]
                            ]
                        ]
                    ];
                }, $items)
            ]
        ]);
    }

    public function saveItemsWithInvoiceId(array $items, int $invoiceId): void
    {
        $this->dynamoDbClient->batchWriteItem([
            'RequestItems' => [
                'awsdeveloper-invoice-items' => array_map(function($item) use ($invoiceId) {
                    return [
                        'PutRequest' => [
                            'Item' => [
                                'InvoiceId' => ['N' => (string) $invoiceId],
                                'Title' => ['S' => (string) $item['title']],
                                'Price' => ['S' => (string) $item['price']],
                                'Quantity' => ['S' => (string) $item['quantity']],
                            ]
                        ]
                    ];
                }, $items)
            ]
        ]);
    }
}