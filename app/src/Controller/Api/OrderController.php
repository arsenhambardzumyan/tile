<?php

namespace App\Controller\Api;

use App\Application\AggregateOrdersUseCase;
use App\Application\GetOrderUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    public function __construct(
        private readonly GetOrderUseCase $getOrder,
        private readonly AggregateOrdersUseCase $aggregateOrders,
    ) {
    }

    #[Route('/api/orders/{id}', name: 'api_get_order', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function getOne(int $id): JsonResponse
    {
        $data = $this->getOrder->execute($id);
        if (!$data) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }
        return new JsonResponse($data);
    }

    #[Route('/api/orders/aggregate', name: 'api_orders_aggregate', methods: ['GET'])]
    public function aggregate(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $perPage = max(1, (int)$request->query->get('per_page', 10));
        $group = (string)$request->query->get('group', 'day');
        return new JsonResponse($this->aggregateOrders->execute($group, $page, $perPage));
    }

}