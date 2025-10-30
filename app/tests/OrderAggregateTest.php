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

final class OrderAggregateTest extends TestCase
{
    public function testAggregateStructure(): void
    {
        $port = $this->createMock(OrderRepositoryPort::class);
        $port->method('aggregateBy')->willReturn([
            'total' => 1,
            'page' => 1,
            'per_page' => 10,
            'pages' => 1,
            'data' => [['group' => '2025-01', 'count' => 1]],
        ]);
        $indexer = $this->createMock(ManticoreIndexer::class);
        $controller = new OrderController(
            new GetOrderUseCase($port),
            new AggregateOrdersUseCase($port),
            new CreateOrderFromSoapUseCase($port, $indexer),
        );

        $req = new Request(['page' => '1', 'per_page' => '10', 'group' => 'month']);
        $resp = $controller->aggregate($req);
        $this->assertSame(200, $resp->getStatusCode());
        $payload = json_decode($resp->getContent(), true);
        $this->assertSame(1, $payload['total']);
        $this->assertSame('2025-01', $payload['data'][0]['group']);
    }
}
