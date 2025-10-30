<?php

namespace App\Application;

use App\Domain\Port\OrderRepositoryPort;

final class AggregateOrdersUseCase
{
    public function __construct(private readonly OrderRepositoryPort $orders)
    {
    }

    /**
     * @return array{total:int,page:int,per_page:int,pages:int,data:list<array{group:string,count:int}>}
     */
    public function execute(string $group, int $page, int $perPage): array
    {
        return $this->orders->aggregateBy($group, $page, $perPage);
    }
}
