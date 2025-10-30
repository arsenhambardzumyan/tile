<?php

namespace App\Application;

use App\Domain\Port\SearchPort;

final class SearchUseCase
{
    public function __construct(private readonly SearchPort $search)
    {
    }

    public function execute(string $q, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $hits = $q !== '' ? $this->search->search('orders', $q, $perPage, $offset) : [];
        return [
            'query' => $q,
            'page' => $page,
            'per_page' => $perPage,
            'results' => $hits,
        ];
    }
}
