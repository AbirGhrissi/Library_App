<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Borrowing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/borrowings', name: 'api_borrowings_')]
class BorrowingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $bookId = $data['bookId'] ?? null;

        if (!$bookId) {
            return $this->json(['error' => 'ID du livre requis'], 400);
        }

        $book = $this->entityManager->getRepository(Book::class)->find($bookId);
        if (!$book) {
            return $this->json(['error' => 'Livre non trouvé'], 404);
        }

        if ($book->getBorrowableQuantity() <= 0) {
            return $this->json(['error' => 'Aucune copie disponible pour emprunt'], 400);
        }

        // Empêcher l'emprunt si l'utilisateur a déjà ce livre (même ISBN) en cours
        $borrowingRepo = $this->entityManager->getRepository(Borrowing::class);
        if ($borrowingRepo->hasActiveBookWithIsbn($user, $book->getIsbn())) {
            return $this->json([
                'error' => 'Vous avez déjà emprunté ce livre (ISBN: ' . $book->getIsbn() . '). Veuillez le retourner avant d\'en emprunter un autre exemplaire.'
            ], 400);
        }

        $borrowing = new Borrowing();
        $borrowing->setUser($user);
        $borrowing->setBook($book);
        $borrowing->setBorrowedAt(new \DateTime());
        $borrowing->setDueDate((new \DateTime())->modify('+14 days'));
        $borrowing->setStatus('active');

        // Réduire la quantité disponible
        $book->setBorrowableQuantity($book->getBorrowableQuantity() - 1);

        $this->entityManager->persist($borrowing);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Livre emprunté avec succès',
            'borrowing' => [
                'id' => $borrowing->getId(),
                'dueDate' => $borrowing->getDueDate()->format('Y-m-d'),
            ]
        ], 201);
    }

    #[Route('/{id}/request-return', name: 'request_return', methods: ['POST'])]
    public function requestReturn(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $borrowing = $this->entityManager->getRepository(Borrowing::class)->find($id);
        
        if (!$borrowing) {
            return $this->json(['error' => 'Emprunt non trouvé'], 404);
        }

        if ($borrowing->getUser() !== $user) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        if ($borrowing->getStatus() !== 'active') {
            return $this->json(['error' => 'Cet emprunt ne peut pas être retourné'], 400);
        }

        // Changer le statut en "pending_return"
        $borrowing->setStatus('pending_return');
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Demande de retour enregistrée. En attente de validation par l\'admin.',
            'status' => 'pending_return'
        ]);
    }

    #[Route('/{id}/approve-return', name: 'approve_return', methods: ['POST'])]
    public function approveReturn(int $id): JsonResponse
    {
        // Vérifier que l'utilisateur est admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $borrowing = $this->entityManager->getRepository(Borrowing::class)->find($id);
        
        if (!$borrowing) {
            return $this->json(['error' => 'Emprunt non trouvé'], 404);
        }

        if ($borrowing->getStatus() !== 'pending_return') {
            return $this->json(['error' => 'Aucune demande de retour en attente pour cet emprunt'], 400);
        }

        // Marquer comme retourné
        $borrowing->setStatus('returned');
        $borrowing->setReturnedAt(new \DateTime());

        // Augmenter la quantité disponible
        $book = $borrowing->getBook();
        $book->setBorrowableQuantity($book->getBorrowableQuantity() + 1);

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Retour du livre approuvé avec succès',
            'status' => 'returned'
        ]);
    }
}
