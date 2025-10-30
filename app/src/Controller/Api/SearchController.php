<?php

namespace App\Controller\Api;

use App\Application\SearchUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    public function __construct(private readonly SearchUseCase $searchUseCase)
    {
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = (string)$request->query->get('q', '');
        $page = max(1, (int)$request->query->get('page', 1));
        $perPage = max(1, (int)$request->query->get('per_page', 10));
        return $this->json($this->searchUseCase->execute($q, $page, $perPage));
    }
}