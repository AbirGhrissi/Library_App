<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findOrCreateForUser(User $user): Cart
    {
        $cart = $this->findOneBy(['user' => $user]);
        
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
        }
        
        return $cart;
    }
}
