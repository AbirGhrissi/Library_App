<?php

namespace App\Controller\Admin;

use App\Entity\Borrowing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApproveReturnController extends AbstractController
{
    #[Route('/admin/approve-return/{id}', name: 'admin_approve_return')]
    public function approveReturn(int $id, EntityManagerInterface $entityManager): Response
    {
        $borrowing = $entityManager->getRepository(Borrowing::class)->find($id);
        
        if (!$borrowing) {
            $this->addFlash('error', 'Emprunt non trouvé');
            return $this->redirectToRoute('admin');
        }

        if ($borrowing->getStatus() !== 'pending_return') {
            $this->addFlash('error', 'Aucune demande de retour en attente pour cet emprunt');
            return $this->redirectToRoute('admin');
        }

        // Marquer comme retourné
        $borrowing->setStatus('returned');
        $borrowing->setReturnedAt(new \DateTime());

        // Augmenter la quantité disponible
        $book = $borrowing->getBook();
        $book->setBorrowableQuantity($book->getBorrowableQuantity() + 1);

        $entityManager->flush();

        $this->addFlash('success', 'Retour du livre approuvé avec succès !');
        
        return $this->redirect($this->generateUrl('admin') . '?crudAction=index&crudControllerFqcn=App\\Controller\\Admin\\BorrowingCrudController');
    }
}
