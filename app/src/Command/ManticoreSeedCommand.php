<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:manticore:seed', description: 'Seed Manticore RT index with existing orders')]
final class ManticoreSeedCommand extends Command
{
    public function __construct(private readonly Connection $conn, private readonly HttpClientInterface $http)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Ensure table exists
        try {
            $this->http->request('POST', 'http://manticore:9308/sql', [
                'body' => [
                    'mode' => 'raw',
                    'query' => "CREATE TABLE IF NOT EXISTS orders (id BIGINT, name TEXT, email TEXT)",
                ],
                'timeout' => 3.0,
            ]);
        } catch (\Throwable) {
            // ignore
        }

        $rows = $this->conn->fetchAllAssociative('SELECT id, name, email FROM orders ORDER BY id LIMIT 10000');
        if (!$rows) {
            $output->writeln('<info>No orders to index</info>');
            return Command::SUCCESS;
        }

        $values = [];
        foreach ($rows as $r) {
            $values[] = sprintf('(%d,%s,%s)', (int)$r['id'], $this->q((string)$r['name']), $this->q((string)($r['email'] ?? '')));
        }
        $chunks = array_chunk($values, 500);
        foreach ($chunks as $chunk) {
            $sql = 'INSERT INTO orders (id,name,email) VALUES ' . implode(',', $chunk);
            try {
                $this->http->request('POST', 'http://manticore:9308/sql', [
                    'body' => [
                        'mode' => 'raw',
                        'query' => $sql,
                    ],
                    'timeout' => 5.0,
                ]);
            } catch (\Throwable) {
                // ignore and continue
            }
        }
        $output->writeln('<info>Seed complete</info>');
        return Command::SUCCESS;
    }

    private function q(string $v): string
    {
        return "'" . str_replace("'", "''", $v) . "'";
    }
}
