<?php

namespace App\Application;

use App\Domain\Port\OrderRepositoryPort;

final class GetOrderUseCase
{
    public function __construct(private readonly OrderRepositoryPort $orders)
    {
    }

    public function execute(int $id): ?array
    {
        $order = $this->orders->findById($id);
        if (!$order) {
            return null;
        }
        return [
            'id' => $order->getId(),
            'hash' => $order->getHash(),
            'status' => $order->getStatus(),
            'email' => $order->getEmail(),
            'name' => $order->getName(),
            'create_date' => $order->getCreateDate()->format(DATE_ATOM),
        ];
    }
}
