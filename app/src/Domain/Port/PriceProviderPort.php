<?php

namespace App\Domain\Port;

interface PriceProviderPort
{
    public function getEuroPrice(string $factory, string $collection, string $article): float;
}
