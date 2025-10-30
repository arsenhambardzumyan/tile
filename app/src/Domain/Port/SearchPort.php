<?php

namespace App\Domain\Port;

interface SearchPort
{
    /**
     * @return array<int, array<string,mixed>>
     */
    public function search(string $index, string $query, int $limit, int $offset): array;
}
