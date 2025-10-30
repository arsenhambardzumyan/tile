<?php

namespace App\Infrastructure\Price;

use App\Domain\Port\PriceProviderPort;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpPriceScraper implements PriceProviderPort
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    public function getEuroPrice(string $factory, string $collection, string $article): float
    {
        $url = sprintf('https://tile.expert/fr/tile/%s/%s/a/%s', $factory, $collection, $article);
        $resp = $this->http->request('GET', $url, [
            'headers' => ['User-Agent' => 'Mozilla/5.0 (TileBot)'],
        ]);
        $status = $resp->getStatusCode();
        if ($status !== 200) {
            throw new \RuntimeException('Price page not found');
        }
        $html = $resp->getContent(false);
        if (preg_match('/([0-9]{1,3}(?:[\.,][0-9]{2}))/u', $html, $m)) {
            return (float)str_replace(',', '.', $m[1]);
        }
        throw new \RuntimeException('Price not found');
    }
}
