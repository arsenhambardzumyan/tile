<?php

namespace App\Controller\Api;

use App\Application\GetPriceUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PriceController extends AbstractController
{
    public function __construct(private readonly GetPriceUseCase $getPrice)
    {
    }

    #[Route('/api/price', name: 'api_price', methods: ['GET'])]
    public function getPrice(Request $request): JsonResponse
    {
        $factory = (string)$request->query->get('factory', '');
        $collection = (string)$request->query->get('collection', '');
        $article = (string)$request->query->get('article', '');
        if ($factory === '' || $collection === '' || $article === '') {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }
        try {
            $data = $this->getPrice->execute($factory, $collection, $article);
            return new JsonResponse($data);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Price fetch failed'], 502);
        }
    }
}