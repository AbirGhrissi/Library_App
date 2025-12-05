<?php

namespace App\Repository;

use App\Entity\Borrowing;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BorrowingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Borrowing::class);
    }

    /**
     * Vérifie si un utilisateur a déjà emprunté un livre avec le même ISBN
     * et ne l'a pas encore retourné
     */
    public function hasActiveBookWithIsbn(User $user, string $isbn): bool
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.book', 'book')
            ->where('b.user = :user')
            ->andWhere('book.isbn = :isbn')
            ->andWhere('b.status IN (:activeStatuses)')
            ->setParameter('user', $user)
            ->setParameter('isbn', $isbn)
            ->setParameter('activeStatuses', ['active', 'pending_return'])
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    /**
     * Récupère tous les emprunts actifs d'un utilisateur
     */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.user = :user')
            ->andWhere('b.status IN (:activeStatuses)')
            ->setParameter('user', $user)
            ->setParameter('activeStatuses', ['active', 'pending_return'])
            ->orderBy('b.borrowedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
