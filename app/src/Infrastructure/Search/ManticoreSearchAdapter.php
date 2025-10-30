<?php

namespace App\Infrastructure\Search;

use App\Domain\Port\SearchPort;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ManticoreSearchAdapter implements SearchPort
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    public function search(string $index, string $query, int $limit, int $offset): array
    {
        try {
            $resp = $this->http->request('POST', 'http://manticore:9308/search', [
                'json' => [
                    'index' => $index,
                    'query' => ['match' => ['*' => $query]],
                    'limit' => $limit,
                    'offset' => $offset,
                ],
                'timeout' => 3.0,
            ]);
            $data = $resp->toArray(false);
            return $data['hits']['hits'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }
}
