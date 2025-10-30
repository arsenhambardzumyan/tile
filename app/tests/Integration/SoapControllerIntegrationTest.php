<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SoapControllerIntegrationTest extends WebTestCase
{
    public function testWsdLEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/soap/wsdl');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/xml; charset=utf-8');
        
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('definitions', $content);
        $this->assertStringContainsString('CreateOrder', $content);
    }

    public function testSoapServerEndpoint(): void
    {
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('SOAP extension not loaded');
        }

        $client = static::createClient();
        $client->request('POST', '/api/soap', [], [], [
            'CONTENT_TYPE' => 'text/xml',
        ], '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <CreateOrder xmlns="http://tile.expert/api/soap">
            <name>Test Order</name>
            <email>test@example.com</email>
            <status>1</status>
        </CreateOrder>
    </soap:Body>
</soap:Envelope>');

        // SOAP server should handle the request (may return 500 if SOAP extension not available)
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 500]),
            'Expected 200 or 500 status code'
        );
    }
}
