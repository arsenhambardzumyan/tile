<?php

namespace App\Infrastructure\Price;

use App\Domain\Port\PriceProviderPort;

final class StubPriceProvider implements PriceProviderPort
{
    public function __construct(private readonly float $stubPrice = 38.99)
    {
    }

    public function getEuroPrice(string $factory, string $collection, string $article): float
    {
        return $this->stubPrice;
    }
}
