<?php

namespace App\Application;

use App\Domain\Port\PriceProviderPort;

final class GetPriceUseCase
{
    public function __construct(private readonly PriceProviderPort $priceProvider)
    {
    }

    public function execute(string $factory, string $collection, string $article): array
    {
        $price = $this->priceProvider->getEuroPrice($factory, $collection, $article);
        return [
            'price' => $price,
            'factory' => $factory,
            'collection' => $collection,
            'article' => $article,
        ];
    }
}
