<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/favorites', name: 'favorites_')]
class FavoriteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['error' => 'Non authentifié'], 401);
            }

            $favorites = $user->getFavoriteBooks();
        
        $booksData = [];
        foreach ($favorites as $book) {
            $authors = $book->getAuthors()->map(fn($author) => [
                'id' => $author->getId(),
                'name' => $author->getName()
            ])->toArray();

            $booksData[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'isbn' => $book->getIsbn(),
                'price' => $book->getPrice(),
                'coverImage' => $book->getCoverImage(),
                'authors' => $authors,
                'author' => !empty($authors) ? $authors[0] : null, // Pour compatibilité
                'publisher' => $book->getPublisher() ? [
                    'id' => $book->getPublisher()->getId(),
                    'name' => $book->getPublisher()->getName()
                ] : null,
                'categories' => $book->getCategories()->map(fn($cat) => [
                    'id' => $cat->getId(),
                    'name' => $cat->getName()
                ])->toArray()
            ];
        }

            return $this->json([
                'favorites' => $booksData,
                'total' => count($booksData)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du chargement des favoris',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'add', methods: ['POST'])]
    public function add(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['error' => 'Livre non trouvé'], 404);
        }

        if ($user->isFavoriteBook($book)) {
            return $this->json(['error' => 'Ce livre est déjà dans vos favoris'], 400);
        }

        $user->addFavoriteBook($book);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Livre ajouté aux favoris',
            'bookId' => $book->getId()
        ], 201);
    }

    #[Route('/{id}', name: 'remove', methods: ['DELETE'])]
    public function remove(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['error' => 'Livre non trouvé'], 404);
        }

        if (!$user->isFavoriteBook($book)) {
            return $this->json(['error' => 'Ce livre n\'est pas dans vos favoris'], 400);
        }

        $user->removeFavoriteBook($book);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Livre retiré des favoris',
            'bookId' => $book->getId()
        ]);
    }

    #[Route('/check/{id}', name: 'check', methods: ['GET'])]
    public function check(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['error' => 'Livre non trouvé'], 404);
        }

        return $this->json([
            'isFavorite' => $user->isFavoriteBook($book)
        ]);
    }
}
