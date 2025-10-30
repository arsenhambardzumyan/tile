<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PriceControllerIntegrationTest extends WebTestCase
{
    public function testPriceEndpointWithValidParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/price?factory=cobsa&collection=manual&article=manu7530bcbm-manualbaltic7-5x30');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('factory', $data);
        $this->assertArrayHasKey('collection', $data);
        $this->assertArrayHasKey('article', $data);
        $this->assertSame('cobsa', $data['factory']);
        $this->assertSame('manual', $data['collection']);
    }

    public function testPriceEndpointWithMissingParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/price?factory=cobsa');

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }
}
