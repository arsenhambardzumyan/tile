<?php

namespace App\Infrastructure\Repository;

use App\Domain\Model\Order as DomainOrder;
use App\Domain\Port\OrderRepositoryPort;
use App\Entity\Order as OrmOrder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOrderRepository implements OrderRepositoryPort
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly Connection $conn)
    {
    }

    public function findById(int $id): ?DomainOrder
    {
        $orm = $this->em->getRepository(OrmOrder::class)->find($id);
        if (!$orm) { return null; }
        return new DomainOrder(
            $orm->getId(),
            $orm->getHash(),
            $orm->getStatus(),
            $orm->getEmail(),
            $orm->getName(),
            $orm->getCreateDate(),
        );
    }

    public function aggregateBy(string $group, int $page, int $perPage): array
    {
        $platform = strtolower($this->conn->getDatabasePlatform()->getName());
        $isSqlite = str_contains($platform, 'sqlite');
        $exprDay = $isSqlite ? "strftime('%Y-%m-%d', o.create_date)" : "DATE_FORMAT(o.create_date, '%Y-%m-%d')";
        $exprMonth = $isSqlite ? "strftime('%Y-%m', o.create_date)" : "DATE_FORMAT(o.create_date, '%Y-%m')";
        $exprYear = $isSqlite ? "strftime('%Y', o.create_date)" : "DATE_FORMAT(o.create_date, '%Y')";
        $groupExpr = match ($group) {
            'day' => $exprDay,
            'month' => $exprMonth,
            'year' => $exprYear,
            default => $exprDay,
        };
        $totalSql = "SELECT COUNT(*) as c FROM (SELECT $groupExpr as g FROM orders o GROUP BY g) t";
        $total = (int)$this->conn->fetchOne($totalSql);
        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT $groupExpr as g, COUNT(*) as cnt FROM orders o GROUP BY g ORDER BY g DESC LIMIT :limit OFFSET :offset";
        $rows = $this->conn->executeQuery($sql, ['limit' => $perPage, 'offset' => $offset], ['limit' => \PDO::PARAM_INT, 'offset' => \PDO::PARAM_INT])->fetchAllAssociative();
        $data = array_map(fn($r) => ['group' => (string)$r['g'], 'count' => (int)$r['cnt']], $rows);
        $pages = $perPage > 0 ? (int)ceil($total / $perPage) : 0;
        return ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => $pages, 'data' => $data];
    }

    public function create(string $name, ?string $email, int $status): array
    {
        $now = new \DateTimeImmutable();
        $hash = substr(sha1($name . microtime(true)), 0, 32);
        $token = substr(sha1(($email ?? '') . microtime(true)), 0, 64);
        
        // Use raw SQL to set all required fields
        $this->conn->executeStatement(
            'INSERT INTO orders (hash, token, name, email, status, pay_type, locale, currency, measure, create_date) 
             VALUES (:hash, :token, :name, :email, :status, :pay_type, :locale, :currency, :measure, :create_date)',
            [
                'hash' => $hash,
                'token' => $token,
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'pay_type' => 1,
                'locale' => 'fr',
                'currency' => 'EUR',
                'measure' => 'm',
                'create_date' => $now->format('Y-m-d H:i:s'),
            ]
        );
        
        $id = (int)$this->conn->lastInsertId();
        return ['id' => $id];
    }
}
