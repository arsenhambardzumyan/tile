<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\PriceScraper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PriceScraperTest extends TestCase
{
    public function testFetchEuroPriceWithCssSelector(): void
    {
        $html = '<div class="price">38,99 €</div>';
        $mockResponse = new MockResponse($html);
        $httpClient = new MockHttpClient([$mockResponse]);
        $scraper = new PriceScraper($httpClient);

        $price = $scraper->fetchEuroPrice('cobsa', 'manual', 'test-article');
        
        $this->assertSame(38.99, $price);
    }

    public function testFetchEuroPriceWithRegexFallback(): void
    {
        $html = '<div>Price: 42.50 EUR</div>';
        $mockResponse = new MockResponse($html);
        $httpClient = new MockHttpClient([$mockResponse]);
        $scraper = new PriceScraper($httpClient);

        $price = $scraper->fetchEuroPrice('cobsa', 'manual', 'test-article');
        
        $this->assertSame(42.50, $price);
    }

    public function testFetchEuroPriceThrowsExceptionWhenNotFound(): void
    {
        $html = '<div>No price here</div>';
        $mockResponse = new MockResponse($html);
        $httpClient = new MockHttpClient([$mockResponse]);
        $scraper = new PriceScraper($httpClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Price not found');
        
        $scraper->fetchEuroPrice('cobsa', 'manual', 'test-article');
    }
}
