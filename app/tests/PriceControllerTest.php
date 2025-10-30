<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\GetPriceUseCase;
use App\Controller\Api\PriceController;
use App\Domain\Port\PriceProviderPort;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PriceControllerTest extends TestCase
{
    public function testGetPriceReturnsJson(): void
    {
        $provider = $this->createMock(PriceProviderPort::class);
        $provider->method('getEuroPrice')->willReturn(38.99);
        $useCase = new GetPriceUseCase($provider);
        $controller = new PriceController($useCase);

        $req = new Request(['factory' => 'cobsa', 'collection' => 'manual', 'article' => 'manu7530bcbm-manualbaltic7-5x30']);
        $resp = $controller->getPrice($req);
        $this->assertSame(200, $resp->getStatusCode());
        $data = json_decode($resp->getContent(), true);
        $this->assertSame(38.99, $data['price']);
        $this->assertSame('cobsa', $data['factory']);
        $this->assertSame('manual', $data['collection']);
    }
}
