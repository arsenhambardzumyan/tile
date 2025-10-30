<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\AggregateOrdersUseCase;
use App\Application\CreateOrderFromSoapUseCase;
use App\Application\GetOrderUseCase;
use App\Controller\Api\OrderController;
use App\Domain\Port\OrderRepositoryPort;
use App\Infrastructure\Search\ManticoreIndexer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class OrderControllerTest extends TestCase
{
    public function testAggregateReturnsShape(): void
    {
        $port = $this->createMock(OrderRepositoryPort::class);
        $port->method('aggregateBy')->willReturn([
            'total' => 2,
            'page' => 1,
            'per_page' => 10,
            'pages' => 1,
            'data' => [['group' => '2024-10-01', 'count' => 2]],
        ]);
        $indexer = $this->createMock(ManticoreIndexer::class);
        $controller = new OrderController(
            new GetOrderUseCase($port),
            new AggregateOrdersUseCase($port),
            new CreateOrderFromSoapUseCase($port, $indexer),
        );

        $resp = $controller->aggregate(new Request(['page' => 1, 'per_page' => 10, 'group' => 'day']));
        $this->assertSame(200, $resp->getStatusCode());
        $data = json_decode($resp->getContent(), true);
        $this->assertSame(2, $data['total']);
        $this->assertSame('2024-10-01', $data['data'][0]['group']);
    }

    public function testGetOneNotFound(): void
    {
        $port = $this->createMock(OrderRepositoryPort::class);
        $port->method('findById')->willReturn(null);
        $indexer = $this->createMock(ManticoreIndexer::class);
        $controller = new OrderController(
            new GetOrderUseCase($port),
            new AggregateOrdersUseCase($port),
            new CreateOrderFromSoapUseCase($port, $indexer),
        );

        $resp = $controller->getOne(999);
        $this->assertSame(404, $resp->getStatusCode());
    }
}
