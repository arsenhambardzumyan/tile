<?php

namespace App\Application;

use App\Domain\Port\OrderRepositoryPort;
use App\Infrastructure\Search\ManticoreIndexer;

final class CreateOrderFromSoapUseCase
{
    public function __construct(private readonly OrderRepositoryPort $orders, private readonly ManticoreIndexer $indexer)
    {
    }

    /**
     * @return array{id:int,hash:string}
     */
    public function execute(string $xml): array
    {
        $doc = new \DOMDocument();
        if (!$doc->loadXML($xml)) {
            throw new \InvalidArgumentException('Invalid XML');
        }
        $xpath = new \DOMXPath($doc);
        $name = $this->val($xpath, '//name') ?? 'Order';
        $email = $this->val($xpath, '//email');
        $status = (int)($this->val($xpath, '//status') ?? 1);

        $created = $this->orders->create($name, $email, $status);
        // index in Manticore (best-effort)
        $this->indexer->indexOrder((int)$created['id'], $name, $email);
        return $created + ['hash' => substr(sha1($name . microtime(true)), 0, 32)];
    }

    private function val(\DOMXPath $xp, string $q): ?string
    {
        $n = $xp->query($q);
        return ($n && $n->length > 0) ? trim((string)$n->item(0)->textContent) : null;
    }
}
