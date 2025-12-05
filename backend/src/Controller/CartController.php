<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Purchase;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/cart', name: 'api_cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartRepository $cartRepository
    ) {
    }

    #[Route('', name: 'get', methods: ['GET'])]
    public function getCart(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $cart = $this->cartRepository->findOrCreateForUser($user);

        $items = [];
        foreach ($cart->getItems() as $item) {
            $book = $item->getBook();
            $items[] = [
                'id' => $item->getId(),
                'book' => [
                    'id' => $book->getId(),
                    'title' => $book->getTitle(),
                    'price' => $book->getPrice(),
                    'coverImage' => $book->getCoverImage(),
                    'authors' => array_map(fn($author) => ['name' => $author->getName()], $book->getAuthors()->toArray()),
                ],
                'quantity' => $item->getQuantity(),
                'subtotal' => $book->getPrice() * $item->getQuantity(),
            ];
        }

        return $this->json([
            'id' => $cart->getId(),
            'items' => $items,
            'total' => $cart->getTotal(),
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addToCart(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $bookId = $data['bookId'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if (!$bookId) {
            return $this->json(['error' => 'ID du livre requis'], 400);
        }

        $book = $this->entityManager->getRepository(Book::class)->find($bookId);
        if (!$book) {
            return $this->json(['error' => 'Livre non trouvé'], 404);
        }

        if ($book->getStockQuantity() < $quantity) {
            return $this->json(['error' => 'Stock insuffisant'], 400);
        }

        $cart = $this->cartRepository->findOrCreateForUser($user);

        // Vérifier si le livre est déjà dans le panier
        $existingItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getBook()->getId() === $book->getId()) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            $newQuantity = $existingItem->getQuantity() + $quantity;
            if ($book->getStockQuantity() < $newQuantity) {
                return $this->json(['error' => 'Stock insuffisant'], 400);
            }
            $existingItem->setQuantity($newQuantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setBook($book);
            $cartItem->setQuantity($quantity);
            $cart->addItem($cartItem);
        }

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $this->json(['message' => 'Livre ajouté au panier', 'cartTotal' => $cart->getTotal()]);
    }

    #[Route('/update/{itemId}', name: 'update', methods: ['PUT'])]
    public function updateQuantity(int $itemId, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 1;

        $cartItem = $this->entityManager->getRepository(CartItem::class)->find($itemId);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        if ($quantity <= 0) {
            $this->entityManager->remove($cartItem);
        } else {
            if ($cartItem->getBook()->getStockQuantity() < $quantity) {
                return $this->json(['error' => 'Stock insuffisant'], 400);
            }
            $cartItem->setQuantity($quantity);
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Panier mis à jour']);
    }

    #[Route('/remove/{itemId}', name: 'remove', methods: ['DELETE'])]
    public function removeFromCart(int $itemId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $cartItem = $this->entityManager->getRepository(CartItem::class)->find($itemId);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();

        return $this->json(['message' => 'Article retiré du panier']);
    }

    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user]);
        if (!$cart || $cart->getItems()->isEmpty()) {
            return $this->json(['error' => 'Panier vide'], 400);
        }

        // Créer les achats à partir du panier
        $purchases = [];
        foreach ($cart->getItems() as $item) {
            $book = $item->getBook();
            
            if ($book->getStockQuantity() < $item->getQuantity()) {
                return $this->json([
                    'error' => 'Stock insuffisant pour ' . $book->getTitle()
                ], 400);
            }

            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setBook($book);
            $purchase->setQuantity($item->getQuantity());
            $purchase->setPrice($book->getPrice());
            $purchase->setStatus('completed');

            // Réduire le stock
            $book->setStockQuantity($book->getStockQuantity() - $item->getQuantity());

            $this->entityManager->persist($purchase);
            $purchases[] = $purchase;
        }

        // Vider le panier
        $cart->clear();

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Commande passée avec succès',
            'purchaseCount' => count($purchases),
            'total' => array_sum(array_map(fn($p) => $p->getPrice() * $p->getQuantity(), $purchases))
        ]);
    }
}
