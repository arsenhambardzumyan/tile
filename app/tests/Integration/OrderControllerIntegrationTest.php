<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerIntegrationTest extends WebTestCase
{
    public function testGetOrderNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/orders/99999');

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testAggregateEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/orders/aggregate?page=1&per_page=10&group=month');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('per_page', $data);
        $this->assertArrayHasKey('pages', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testAggregateWithInvalidGroup(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/orders/aggregate?group=invalid');

        $this->assertResponseIsSuccessful();
        // Should fallback to 'day' grouping
    }
}
