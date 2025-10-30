<?php

namespace App\Infrastructure\Search;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ManticoreIndexer
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    public function indexOrder(int $id, string $name, ?string $email): void
    {
        $sql = sprintf(
            "INSERT INTO orders (id,name,email) VALUES (%d,%s,%s)",
            $id,
            $this->q($name),
            $this->q($email ?? '')
        );
        try {
            $this->http->request('POST', 'http://manticore:9308/sql', [
                'body' => [
                    'mode' => 'raw',
                    'query' => $sql,
                ],
                'timeout' => 3.0,
            ]);
        } catch (\Throwable) {
            // ignore indexing errors
        }
    }

    private function q(string $v): string
    {
        return "'" . str_replace("'", "''", $v) . "'";
    }
}
