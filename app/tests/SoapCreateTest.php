<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\CreateOrderFromSoapUseCase;
use App\Controller\Api\SoapController;
use App\Domain\Port\OrderRepositoryPort;
use App\Infrastructure\Search\ManticoreIndexer;
use PHPUnit\Framework\TestCase;

final class SoapCreateTest extends TestCase
{
    public function testSoapCreateMinimal(): void
    {
        $port = $this->createMock(OrderRepositoryPort::class);
        $port->expects($this->once())->method('create')->willReturn(['id' => 1, 'hash' => 'test-hash']);
        $indexer = $this->createMock(ManticoreIndexer::class);
        $indexer->expects($this->once())->method('indexOrder');

        $useCase = new CreateOrderFromSoapUseCase($port, $indexer);
        $controller = new SoapController($useCase);

        $result = $controller->CreateOrder('Test', 'a@b.c', 2);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('hash', $result);
        $this->assertSame(1, $result['id']);
    }
}
