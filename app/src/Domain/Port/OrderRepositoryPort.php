<?php

namespace App\Domain\Port;

use App\Domain\Model\Order;

interface OrderRepositoryPort
{
    public function findById(int $id): ?Order;

    /**
     * @return array{total:int,page:int,per_page:int,pages:int,data:list<array{group:string,count:int}>}
     */
    public function aggregateBy(string $group, int $page, int $perPage): array;

    /**
     * @return array{ id:int }
     */
    public function create(string $name, ?string $email, int $status): array;
}
