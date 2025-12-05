<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/books', name: 'api_books_')]
class BookSearchController extends AbstractController
{
    public function __construct(private BookRepository $bookRepository)
    {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function advancedSearch(Request $request): JsonResponse
    {
        $criteria = [
            'title' => $request->query->get('title'),
            'author' => $request->query->get('author'),
            'isbn' => $request->query->get('isbn'),
            'category' => $request->query->get('category'),
            'publisher' => $request->query->get('publisher'),
            'minPrice' => $request->query->get('minPrice'),
            'maxPrice' => $request->query->get('maxPrice'),
        ];
        $criteria = array_filter($criteria, fn($value) => $value !== null && $value !== '');

        $books = $this->bookRepository->advancedSearch($criteria);

        return $this->json($books, 200, [], ['groups' => ['book:read']]);
    }
}
