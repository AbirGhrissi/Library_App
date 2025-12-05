<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function advancedSearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'a')
            ->leftJoin('b.publisher', 'p')
            ->leftJoin('b.categories', 'c');

        if (!empty($criteria['title'])) {
            $qb->andWhere('b.title LIKE :title')
               ->setParameter('title', '%' . $criteria['title'] . '%');
        }

        if (!empty($criteria['author'])) {
            $qb->andWhere('a.name LIKE :author')
               ->setParameter('author', '%' . $criteria['author'] . '%');
        }

        if (!empty($criteria['isbn'])) {
            $qb->andWhere('b.isbn = :isbn')
               ->setParameter('isbn', $criteria['isbn']);
        }

        if (!empty($criteria['category'])) {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $criteria['category']);
        }

        if (!empty($criteria['publisher'])) {
            $qb->andWhere('p.id = :publisher')
               ->setParameter('publisher', $criteria['publisher']);
        }

        if (isset($criteria['minPrice'])) {
            $qb->andWhere('b.price >= :minPrice')
               ->setParameter('minPrice', $criteria['minPrice']);
        }

        if (isset($criteria['maxPrice'])) {
            $qb->andWhere('b.price <= :maxPrice')
               ->setParameter('maxPrice', $criteria['maxPrice']);
        }

        return $qb->getQuery()->getResult();
    }
}
