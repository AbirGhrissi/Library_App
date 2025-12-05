<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user', name: 'api_user_')]
class UserBooksController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/borrowings', name: 'borrowings', methods: ['GET'])]
    public function getBorrowings(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $borrowings = $user->getBorrowings();
        $data = [];

        foreach ($borrowings as $borrowing) {
            $book = $borrowing->getBook();
            $data[] = [
                'id' => $borrowing->getId(),
                'borrowedAt' => $borrowing->getBorrowedAt()->format('Y-m-d H:i:s'),
                'dueDate' => $borrowing->getDueDate()->format('Y-m-d H:i:s'),
                'returnedAt' => $borrowing->getReturnedAt()?->format('Y-m-d H:i:s'),
                'status' => $borrowing->getStatus(),
                'book' => [
                    'id' => $book->getId(),
                    'title' => $book->getTitle(),
                    'isbn' => $book->getIsbn(),
                    'authors' => array_map(fn($author) => ['name' => $author->getName()], $book->getAuthors()->toArray()),
                    'categories' => array_map(fn($category) => ['name' => $category->getName()], $book->getCategories()->toArray())
                ]
            ];
        }

        return $this->json($data);
    }

    #[Route('/purchases', name: 'purchases', methods: ['GET'])]
    public function getPurchases(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $purchases = $user->getPurchases();
        $data = [];

        foreach ($purchases as $purchase) {
            $book = $purchase->getBook();
            $data[] = [
                'id' => $purchase->getId(),
                'purchasedAt' => $purchase->getPurchasedAt()->format('Y-m-d H:i:s'),
                'price' => $purchase->getPrice(),
                'quantity' => $purchase->getQuantity(),
                'status' => $purchase->getStatus(),
                'book' => [
                    'id' => $book->getId(),
                    'title' => $book->getTitle(),
                    'isbn' => $book->getIsbn(),
                    'authors' => array_map(fn($author) => ['name' => $author->getName()], $book->getAuthors()->toArray()),
                    'categories' => array_map(fn($category) => ['name' => $category->getName()], $book->getCategories()->toArray())
                ]
            ];
        }

        return $this->json($data);
    }
}
