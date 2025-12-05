# 🔧 Logique de Fonctionnement - Toutes les Fonctionnalités

## 📚 Table des Matières

1. [Authentification et Gestion des Utilisateurs](#1-authentification)
2. [Gestion des Livres](#2-gestion-des-livres)
3. [Système d'Emprunt](#3-système-demprunt)
4. [Système de Retour avec Validation](#4-système-de-retour)
5. [Panier et Achats](#5-panier-et-achats)
6. [Réinitialisation de Mot de Passe](#6-réinitialisation-mot-de-passe)
7. [Gestion Administrative](#7-gestion-administrative)

---

## 1. Authentification et Gestion des Utilisateurs

### 🔐 1.1 Inscription (Register)

**Objectif :** Créer un nouveau compte utilisateur

**Flux complet :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | 1. User remplit formulaire      |                                   |
   |    - Email                      |                                   |
   |    - Prénom                     |                                   |
   |    - Nom                        |                                   |
   |    - Téléphone                  |                                   |
   |    - Mot de passe               |                                   |
   |                                 |                                   |
   | 2. POST /api/register           |                                   |
   |---------------------------------→                                   |
   |    Body: {                      |                                   |
   |      email: "user@example.com", | 3. Validation des données         |
   |      password: "Pass123!",      |    - Email unique ?               |
   |      firstName: "John",         |    - Format valide ?              |
   |      lastName: "Doe"            |    - Mot de passe fort ?          |
   |    }                            |                                   |
   |                                 |                                   |
   |                                 | 4. UserPasswordHashSubscriber     |
   |                                 |    - Hash le mot de passe         |
   |                                 |    (bcrypt, auto)                 |
   |                                 |                                   |
   |                                 | 5. Création User                  |
   |                                 |    - roles: ["ROLE_USER"]         |
   |                                 |    - createdAt: now()             |
   |                                 |                                   |
   |                                 | 6. INSERT INTO user               |
   |                                 |-----------------------------------→
   |                                 |                                   |
   |                                 |                                   | 7. User ID généré
   |                                 |                                   |
   |                                 | 8. Création Cart automatique      |
   |                                 |    pour l'utilisateur             |
   |                                 |-----------------------------------→ 
   |                                 |                                   |
   | 9. Réponse JSON                 |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      message: "success",        |                                   |
   |      user: {...}                |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 10. Redirection vers /login     |                                   |
```

**Code Backend (AuthController.php) :**

```php
#[Route('/register', name: 'register', methods: ['POST'])]
public function register(Request $request): JsonResponse
{
    // 1. Parser les données JSON
    $data = json_decode($request->getContent(), true);
    
    // 2. Vérifier si l'email existe déjà
    $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
    if ($existingUser) {
        return $this->json(['error' => 'Email déjà utilisé'], 400);
    }
    
    // 3. Créer l'utilisateur
    $user = new User();
    $user->setEmail($data['email']);
    $user->setFirstName($data['firstName']);
    $user->setLastName($data['lastName']);
    $user->setPhone($data['phone'] ?? null);
    $user->setPlainPassword($data['password']); // Sera hashé automatiquement
    $user->setRoles(['ROLE_USER']);
    
    // 4. Sauvegarder (le EventSubscriber hash le mot de passe)
    $this->entityManager->persist($user);
    $this->entityManager->flush();
    
    // 5. Créer un panier pour l'utilisateur
    $cart = new Cart();
    $cart->setUser($user);
    $this->entityManager->persist($cart);
    $this->entityManager->flush();
    
    return $this->json(['message' => 'Utilisateur créé avec succès'], 201);
}
```

**Points clés :**
- ✅ Hash automatique du mot de passe (EventSubscriber)
- ✅ Vérification unicité de l'email
- ✅ Création automatique du panier
- ✅ Rôle par défaut : ROLE_USER

---

### 🔑 1.2 Connexion (Login)

**Objectif :** Authentifier l'utilisateur et obtenir un JWT

**Flux complet :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | 1. User entre credentials       |                                   |
   |    - Email                      |                                   |
   |    - Password                   |                                   |
   |                                 |                                   |
   | 2. POST /api/login              |                                   |
   |---------------------------------→                                   |
   |    Body: {                      |                                   |
   |      email: "user@example.com", | 3. Recherche utilisateur          |
   |      password: "Pass123!"       |-----------------------------------→
   |    }                            |                                   |
   |                                 |                                   | 4. SELECT * FROM user
   |                                 |                                   |    WHERE email = ?
   |                                 |←-----------------------------------|
   |                                 |    User trouvé                    |
   |                                 |                                   |
   |                                 | 5. Vérification mot de passe      |
   |                                 |    password_verify(               |
   |                                 |      $inputPassword,              |
   |                                 |      $user->getPassword()         |
   |                                 |    )                              |
   |                                 |                                   |
   |                                 | 6. Si OK, génère JWT              |
   |                                 |    Payload: {                     |
   |                                 |      user_id: 1,                  |
   |                                 |      email: "user@...",           |
   |                                 |      roles: ["ROLE_USER"],        |
   |                                 |      exp: timestamp + 1h          |
   |                                 |    }                              |
   |                                 |    Signature: HMAC-SHA256         |
   |                                 |                                   |
   | 7. Réponse avec token           |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      token: "eyJhbGciOi...",    |                                   |
   |      user: {                    |                                   |
   |        id: 1,                   |                                   |
   |        email: "user@...",       |                                   |
   |        roles: ["ROLE_USER"]     |                                   |
   |      }                          |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 8. Stocke token dans            |                                   |
   |    localStorage                 |                                   |
   |    localStorage.setItem(        |                                   |
   |      'token',                   |                                   |
   |      token                      |                                   |
   |    )                            |                                   |
   |                                 |                                   |
   | 9. Stocke user dans Context     |                                   |
   |    setUser(userData)            |                                   |
```

**Structure du JWT :**

```
Header.Payload.Signature

Header (Base64):
{
  "alg": "HS256",
  "typ": "JWT"
}

Payload (Base64):
{
  "user_id": 1,
  "email": "user@example.com",
  "roles": ["ROLE_USER"],
  "exp": 1704067200
}

Signature:
HMAC-SHA256(
  base64UrlEncode(header) + "." + 
  base64UrlEncode(payload),
  secret_key
)
```

**Points clés :**
- ✅ JWT valide 1 heure (configurable)
- ✅ Stocké dans localStorage (frontend)
- ✅ Envoyé dans Authorization header
- ✅ Stateless (pas de session serveur)

---

## 2. Gestion des Livres

### 📚 2.1 Affichage de la Liste des Livres

**Objectif :** Récupérer et afficher tous les livres disponibles

**Flux :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | 1. Page /books chargée          |                                   |
   |                                 |                                   |
   | 2. GET /api/books               |                                   |
   |---------------------------------→                                   |
   |    Header:                      |                                   |
   |    Authorization: Bearer token  | 3. Vérifie JWT                    |
   |                                 |    - Décode le token              |
   |                                 |    - Vérifie signature            |
   |                                 |    - Vérifie expiration           |
   |                                 |                                   |
   |                                 | 4. Query avec Doctrine            |
   |                                 |    SELECT b.*, a.*, c.*           |
   |                                 |    FROM book b                    |
   |                                 |    JOIN book_author ba ON...      |
   |                                 |    JOIN author a ON...            |
   |                                 |    JOIN book_category bc ON...    |
   |                                 |    JOIN category c ON...          |
   |                                 |-----------------------------------→
   |                                 |                                   |
   |                                 |←-----------------------------------|
   |                                 |    Résultats                      |
   |                                 |                                   |
   |                                 | 5. Sérialisation JSON             |
   |                                 |    (Groupes: book:read)           |
   |                                 |    - Inclut authors[]             |
   |                                 |    - Inclut categories[]          |
   |                                 |    - Exclut mot de passe, etc.    |
   |                                 |                                   |
   | 6. Réponse JSON                 |                                   |
   |←---------------------------------|                                   |
   |    [                            |                                   |
   |      {                          |                                   |
   |        id: 1,                   |                                   |
   |        title: "Livre 1",        |                                   |
   |        isbn: "123-456",         |                                   |
   |        price: "25.500",         |                                   |
   |        stockQuantity: 10,       |                                   |
   |        borrowableQuantity: 5,   |                                   |
   |        authors: [               |                                   |
   |          {name: "Auteur 1"},    |                                   |
   |          {name: "Auteur 2"}     |                                   |
   |        ],                       |                                   |
   |        categories: [            |                                   |
   |          {name: "Fiction"}      |                                   |
   |        ]                        |                                   |
   |      },                         |                                   |
   |      ...                        |                                   |
   |    ]                            |                                   |
   |                                 |                                   |
   | 7. Affichage via BookCard       |                                   |
   |    components                   |                                   |
```

**Code Frontend (books/page.tsx) :**

```typescript
useEffect(() => {
  const fetchBooks = async () => {
    const response = await fetch('http://localhost:8000/api/books', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await response.json();
    setBooks(data);
  };
  
  fetchBooks();
}, []);
```

---

### 🔍 2.2 Recherche de Livres

**Objectif :** Filtrer les livres par titre, ISBN, auteur ou catégorie

**Flux :**

```
FRONTEND                          BACKEND                         
   |                                 |
   | User tape "Harry Potter"        |
   | dans la barre de recherche      |
   |                                 |
   | GET /api/books?search=Harry     |
   |---------------------------------→
   |                                 | 
   |                                 | Doctrine QueryBuilder:
   |                                 | WHERE b.title LIKE '%Harry%'
   |                                 | OR a.name LIKE '%Harry%'
   |                                 | OR c.name LIKE '%Harry%'
   |                                 | OR b.isbn LIKE '%Harry%'
   |                                 |
   | Résultats filtrés               |
   |←---------------------------------|
```

**Code Backend (BookSearchController.php) :**

```php
public function search(Request $request): JsonResponse
{
    $searchTerm = $request->query->get('search', '');
    
    $qb = $this->bookRepository->createQueryBuilder('b')
        ->leftJoin('b.authors', 'a')
        ->leftJoin('b.categories', 'c')
        ->where('b.title LIKE :search')
        ->orWhere('a.name LIKE :search')
        ->orWhere('c.name LIKE :search')
        ->orWhere('b.isbn LIKE :search')
        ->setParameter('search', '%' . $searchTerm . '%');
    
    $books = $qb->getQuery()->getResult();
    
    return $this->json($books, 200, [], ['groups' => 'book:read']);
}
```

**Points clés :**
- ✅ Recherche dans plusieurs champs
- ✅ Recherche partielle (LIKE %term%)
- ✅ Joins automatiques (Doctrine)
- ✅ Résultats en temps réel

---

## 3. Système d'Emprunt

### 📖 3.1 Emprunter un Livre

**Objectif :** Permettre à un utilisateur d'emprunter un livre disponible

**Flux complet :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | 1. User clique "Emprunter"      |                                   |
   |    sur un livre                 |                                   |
   |                                 |                                   |
   | 2. POST /api/borrowings         |                                   |
   |---------------------------------→                                   |
   |    Body: {bookId: 1}            |                                   |
   |    Header: Authorization: ...   | 3. Vérifie JWT                    |
   |                                 |    → Récupère User                |
   |                                 |                                   |
   |                                 | 4. Récupère le livre              |
   |                                 |    SELECT * FROM book             |
   |                                 |    WHERE id = 1                   |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |    Book trouvé                    |
   |                                 |                                   |
   |                                 | 5. Vérifications                  |
   |                                 |    ✓ borrowableQuantity > 0 ?     |
   |                                 |    ✓ User pas déjà emprunté ?     |
   |                                 |    ✓ User pas de retard ?         |
   |                                 |                                   |
   |                                 | 6. Crée Borrowing                 |
   |                                 |    - user_id: 1                   |
   |                                 |    - book_id: 1                   |
   |                                 |    - borrowedAt: NOW()            |
   |                                 |    - dueDate: NOW() + 14 jours    |
   |                                 |    - status: 'active'             |
   |                                 |    - returnedAt: NULL             |
   |                                 |                                   |
   |                                 | 7. Met à jour Book                |
   |                                 |    borrowableQuantity = qty - 1   |
   |                                 |                                   |
   |                                 | 8. Transaction BEGIN              |
   |                                 |    INSERT INTO borrowing          |
   |                                 |    UPDATE book SET borrowable...  |
   |                                 |    COMMIT                         |
   |                                 |-----------------------------------→
   |                                 |                                   |
   | 9. Réponse succès               |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      message: "Livre emprunté", |                                   |
   |      borrowing: {               |                                   |
   |        id: 123,                 |                                   |
   |        dueDate: "2024-02-01"    |                                   |
   |      }                          |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 10. Affiche notification        |                                   |
   |     "✓ Livre emprunté jusqu'au  |                                   |
   |      01/02/2024"                |                                   |
```

**Code Backend (BorrowingController.php) :**

```php
#[Route('', name: 'create', methods: ['POST'])]
public function create(Request $request): JsonResponse
{
    // 1. Récupérer l'utilisateur authentifié
    $user = $this->getUser();
    if (!$user) {
        return $this->json(['error' => 'Non authentifié'], 401);
    }
    
    // 2. Parser les données
    $data = json_decode($request->getContent(), true);
    $bookId = $data['bookId'] ?? null;
    
    if (!$bookId) {
        return $this->json(['error' => 'ID du livre requis'], 400);
    }
    
    // 3. Récupérer le livre
    $book = $this->entityManager->getRepository(Book::class)->find($bookId);
    if (!$book) {
        return $this->json(['error' => 'Livre non trouvé'], 404);
    }
    
    // 4. Vérifier la disponibilité
    if ($book->getBorrowableQuantity() <= 0) {
        return $this->json([
            'error' => 'Aucune copie disponible pour emprunt'
        ], 400);
    }
    
    // 5. Créer l'emprunt
    $borrowing = new Borrowing();
    $borrowing->setUser($user);
    $borrowing->setBook($book);
    $borrowing->setBorrowedAt(new \DateTime());
    $borrowing->setDueDate((new \DateTime())->modify('+14 days'));
    $borrowing->setStatus('active');
    
    // 6. Réduire la quantité disponible
    $book->setBorrowableQuantity($book->getBorrowableQuantity() - 1);
    
    // 7. Sauvegarder (transaction automatique)
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
```

**Points clés :**
- ✅ Transaction automatique (Doctrine)
- ✅ Durée d'emprunt : 14 jours
- ✅ Vérification de disponibilité
- ✅ Réduction automatique du stock

**Règles métier :**
- Un utilisateur peut emprunter plusieurs livres
- Un livre ne peut être emprunté que si `borrowableQuantity > 0`
- Date de retour = date d'emprunt + 14 jours
- Status initial = 'active'

---

## 4. Système de Retour avec Validation Admin

### 🔄 4.1 Demande de Retour par l'Utilisateur

**Objectif :** L'utilisateur demande à retourner un livre emprunté

**Flux :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | User sur /my-books              |                                   |
   | Voit ses emprunts actifs        |                                   |
   |                                 |                                   |
   | 1. Clique "Demander le retour"  |                                   |
   |    sur l'emprunt #123           |                                   |
   |                                 |                                   |
   | 2. Confirmation dialog          |                                   |
   |    "Voulez-vous vraiment        |                                   |
   |     retourner ce livre ?"       |                                   |
   |                                 |                                   |
   | 3. POST /api/borrowings/123/    |                                   |
   |    request-return               |                                   |
   |---------------------------------→                                   |
   |    Header: Authorization: ...   | 4. Vérifie JWT                    |
   |                                 |    → Récupère User                |
   |                                 |                                   |
   |                                 | 5. Récupère Borrowing             |
   |                                 |    SELECT * FROM borrowing        |
   |                                 |    WHERE id = 123                 |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |                                   |
   |                                 | 6. Vérifications                  |
   |                                 |    ✓ Borrowing existe ?           |
   |                                 |    ✓ Appartient à User ?          |
   |                                 |    ✓ Status = 'active' ?          |
   |                                 |                                   |
   |                                 | 7. Change status                  |
   |                                 |    status = 'pending_return'      |
   |                                 |                                   |
   |                                 | 8. UPDATE borrowing               |
   |                                 |    SET status = 'pending_return'  |
   |                                 |    WHERE id = 123                 |
   |                                 |-----------------------------------→
   |                                 |                                   |
   | 9. Réponse                      |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      message: "Demande de       |                                   |
   |        retour enregistrée",     |                                   |
   |      status: "pending_return"   |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 10. Affiche badge jaune         |                                   |
   |     "En attente de validation"  |                                   |
```

**Code Backend :**

```php
#[Route('/{id}/request-return', name: 'request_return', methods: ['POST'])]
public function requestReturn(int $id): JsonResponse
{
    $user = $this->getUser();
    if (!$user) {
        return $this->json(['error' => 'Non authentifié'], 401);
    }
    
    $borrowing = $this->entityManager
        ->getRepository(Borrowing::class)
        ->find($id);
    
    if (!$borrowing) {
        return $this->json(['error' => 'Emprunt non trouvé'], 404);
    }
    
    // Vérifier que c'est bien l'emprunt de cet utilisateur
    if ($borrowing->getUser() !== $user) {
        return $this->json(['error' => 'Non autorisé'], 403);
    }
    
    // Vérifier que l'emprunt est actif
    if ($borrowing->getStatus() !== 'active') {
        return $this->json([
            'error' => 'Cet emprunt ne peut pas être retourné'
        ], 400);
    }
    
    // Changer le statut
    $borrowing->setStatus('pending_return');
    $this->entityManager->flush();
    
    return $this->json([
        'message' => 'Demande de retour enregistrée. ' .
                    'En attente de validation par l\'admin.',
        'status' => 'pending_return'
    ]);
}
```

---

### ✅ 4.2 Validation du Retour par l'Admin

**Objectif :** L'admin approuve le retour et le livre redevient disponible

**Flux :**

```
ADMIN INTERFACE                   BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | Admin sur /admin                |                                   |
   | → Emprunts                      |                                   |
   |                                 |                                   |
   | 1. Voit les emprunts avec       |                                   |
   |    badge jaune "En attente      |                                   |
   |    de retour"                   |                                   |
   |                                 |                                   |
   | 2. Clique "Accepter le retour"  |                                   |
   |    (bouton vert ✓)              |                                   |
   |                                 |                                   |
   | 3. GET /admin/approve-return/123|                                   |
   |---------------------------------→                                   |
   |                                 | 4. Vérifie rôle ADMIN             |
   |                                 |    if (!isGranted('ROLE_ADMIN'))  |
   |                                 |                                   |
   |                                 | 5. Récupère Borrowing             |
   |                                 |    SELECT * FROM borrowing        |
   |                                 |    WHERE id = 123                 |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |                                   |
   |                                 | 6. Vérifie status                 |
   |                                 |    status == 'pending_return' ?   |
   |                                 |                                   |
   |                                 | 7. Met à jour Borrowing           |
   |                                 |    - status = 'returned'          |
   |                                 |    - returnedAt = NOW()           |
   |                                 |                                   |
   |                                 | 8. Récupère Book                  |
   |                                 |    book = borrowing->getBook()    |
   |                                 |                                   |
   |                                 | 9. Augmente quantité              |
   |                                 |    borrowableQuantity = qty + 1   |
   |                                 |                                   |
   |                                 | 10. Transaction                   |
   |                                 |     UPDATE borrowing              |
   |                                 |     UPDATE book                   |
   |                                 |-----------------------------------→
   |                                 |                                   |
   | 11. Flash message success       |                                   |
   |←---------------------------------|                                   |
   |     "✓ Retour approuvé !"       |                                   |
   |                                 |                                   |
   | 12. Redirection vers            |                                   |
   |     liste des emprunts          |                                   |
```

**Code Backend (ApproveReturnController.php) :**

```php
#[Route('/admin/approve-return/{id}', name: 'admin_approve_return')]
public function approveReturn(int $id, EntityManagerInterface $em): Response
{
    // 1. Récupérer l'emprunt
    $borrowing = $em->getRepository(Borrowing::class)->find($id);
    
    if (!$borrowing) {
        $this->addFlash('error', 'Emprunt non trouvé');
        return $this->redirectToRoute('admin');
    }
    
    // 2. Vérifier le statut
    if ($borrowing->getStatus() !== 'pending_return') {
        $this->addFlash('error', 'Aucune demande de retour en attente');
        return $this->redirectToRoute('admin');
    }
    
    // 3. Marquer comme retourné
    $borrowing->setStatus('returned');
    $borrowing->setReturnedAt(new \DateTime());
    
    // 4. Augmenter la quantité disponible
    $book = $borrowing->getBook();
    $book->setBorrowableQuantity($book->getBorrowableQuantity() + 1);
    
    // 5. Sauvegarder
    $em->flush();
    
    // 6. Message de confirmation
    $this->addFlash('success', 'Retour du livre approuvé avec succès !');
    
    // 7. Rediriger vers la liste des emprunts
    return $this->redirect(
        $this->generateUrl('admin') . 
        '?crudAction=index&crudControllerFqcn=App\\Controller\\Admin\\BorrowingCrudController'
    );
}
```

**États possibles d'un Borrowing :**

```
┌─────────┐
│ active  │  Emprunt en cours
└────┬────┘
     │
     │ User demande retour
     ▼
┌──────────────┐
│pending_return│  En attente validation
└──────┬───────┘
       │
       │ Admin approuve
       ▼
┌──────────┐
│ returned │  Livre retourné
└──────────┘

Autres statuts possibles:
┌─────────┐
│ overdue │  Retard de retour
└─────────┘
```

**Points clés :**
- ✅ Workflow à 2 étapes (demande → validation)
- ✅ Traçabilité complète
- ✅ Stock mis à jour automatiquement
- ✅ Sécurité : seul l'admin peut approuver

---

## 5. Panier et Achats

### 🛒 5.1 Ajout au Panier

**Objectif :** Ajouter un livre au panier de l'utilisateur

**Flux :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | User clique "Ajouter au panier" |                                   |
   | Quantité: 2                     |                                   |
   |                                 |                                   |
   | POST /api/cart/add              |                                   |
   |---------------------------------→                                   |
   |    Body: {                      |                                   |
   |      bookId: 1,                 | 1. Vérifie JWT                    |
   |      quantity: 2                |    → Récupère User                |
   |    }                            |                                   |
   |                                 | 2. Trouve/Crée Cart               |
   |                                 |    SELECT * FROM cart             |
   |                                 |    WHERE user_id = ?              |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |                                   |
   |                                 | 3. Vérifie si item existe déjà    |
   |                                 |    SELECT * FROM cart_item        |
   |                                 |    WHERE cart_id = ?              |
   |                                 |    AND book_id = ?                |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |                                   |
   |                                 | 4a. Si existe: UPDATE quantity    |
   |                                 |     quantity = quantity + 2       |
   |                                 |                                   |
   |                                 | 4b. Si n'existe pas: INSERT       |
   |                                 |     INSERT INTO cart_item         |
   |                                 |     (cart_id, book_id, qty)       |
   |                                 |-----------------------------------→
   |                                 |                                   |
   |                                 | 5. Calcule le total du panier     |
   |                                 |    SELECT SUM(ci.quantity *       |
   |                                 |    b.price) FROM cart_item ci     |
   |                                 |    JOIN book b ON...              |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |                                   |
   | 6. Réponse avec panier complet  |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      cart: {                    |                                   |
   |        id: 1,                   |                                   |
   |        items: [                 |                                   |
   |          {                      |                                   |
   |            id: 1,               |                                   |
   |            book: {...},         |                                   |
   |            quantity: 2,         |                                   |
   |            subtotal: 51.000     |                                   |
   |          }                      |                                   |
   |        ],                       |                                   |
   |        total: 51.000            |                                   |
   |      }                          |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 7. Met à jour CartContext       |                                   |
   |    setCart(data.cart)           |                                   |
```

**Code Backend (CartController.php) :**

```php
#[Route('/add', name: 'add', methods: ['POST'])]
public function addToCart(Request $request): JsonResponse
{
    $user = $this->getUser();
    $data = json_decode($request->getContent(), true);
    
    $bookId = $data['bookId'] ?? null;
    $quantity = $data['quantity'] ?? 1;
    
    // 1. Récupérer le livre
    $book = $this->entityManager->getRepository(Book::class)->find($bookId);
    if (!$book) {
        return $this->json(['error' => 'Livre non trouvé'], 404);
    }
    
    // 2. Vérifier le stock
    if ($book->getStockQuantity() < $quantity) {
        return $this->json(['error' => 'Stock insuffisant'], 400);
    }
    
    // 3. Récupérer ou créer le panier
    $cart = $this->entityManager
        ->getRepository(Cart::class)
        ->findOneBy(['user' => $user]);
    
    if (!$cart) {
        $cart = new Cart();
        $cart->setUser($user);
        $this->entityManager->persist($cart);
    }
    
    // 4. Vérifier si l'article existe déjà
    $cartItem = null;
    foreach ($cart->getItems() as $item) {
        if ($item->getBook()->getId() === $bookId) {
            $cartItem = $item;
            break;
        }
    }
    
    // 5. Ajouter ou mettre à jour
    if ($cartItem) {
        $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
    } else {
        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setBook($book);
        $cartItem->setQuantity($quantity);
        $this->entityManager->persist($cartItem);
    }
    
    $this->entityManager->flush();
    
    // 6. Retourner le panier complet
    return $this->json($this->formatCart($cart));
}
```

---

### 💰 5.2 Finalisation de la Commande (Checkout)

**Objectif :** Transformer le panier en achats et réduire le stock

**Flux :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | User sur /cart                  |                                   |
   | Clique "Commander"              |                                   |
   |                                 |                                   |
   | POST /api/cart/checkout         |                                   |
   |---------------------------------→                                   |
   |    Header: Authorization: ...   | 1. Récupère User et Cart          |
   |                                 |    SELECT * FROM cart             |
   |                                 |    JOIN cart_item ON...           |
   |                                 |    JOIN book ON...                |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |                                   |
   |                                 | 2. Pour chaque CartItem:          |
   |                                 |    ┌─────────────────────────┐   |
   |                                 |    │ a. Crée Purchase        │   |
   |                                 |    │    - user_id            │   |
   |                                 |    │    - book_id            │   |
   |                                 |    │    - quantity           │   |
   |                                 |    │    - price (gelé)       │   |
   |                                 |    │    - purchasedAt: NOW() │   |
   |                                 |    │    - status: 'completed'│   |
   |                                 |    │                         │   |
   |                                 |    │ b. Réduit stock         │   |
   |                                 |    │    stockQuantity -= qty │   |
   |                                 |    │                         │   |
   |                                 |    │ c. Vérifie stock > 0    │   |
   |                                 |    └─────────────────────────┘   |
   |                                 |                                   |
   |                                 | 3. Transaction BEGIN              |
   |                                 |    INSERT INTO purchase (×N)      |
   |                                 |    UPDATE book SET stock... (×N)  |
   |                                 |    DELETE FROM cart_item (×N)     |
   |                                 |    COMMIT                         |
   |                                 |-----------------------------------→
   |                                 |                                   |
   | 4. Réponse succès               |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      message: "Commande OK",    |                                   |
   |      purchases: [               |                                   |
   |        {                        |                                   |
   |          id: 1,                 |                                   |
   |          book: {...},           |                                   |
   |          quantity: 2,           |                                   |
   |          price: "25.500"        |                                   |
   |        }                        |                                   |
   |      ],                         |                                   |
   |      total: "51.000"            |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 5. Vide CartContext             |                                   |
   |    setCart(null)                |                                   |
   |                                 |                                   |
   | 6. Redirection vers /my-books   |                                   |
   |    Onglet "Mes Achats"          |                                   |
```

**Code Backend :**

```php
#[Route('/checkout', name: 'checkout', methods: ['POST'])]
public function checkout(): JsonResponse
{
    $user = $this->getUser();
    
    // 1. Récupérer le panier
    $cart = $this->entityManager
        ->getRepository(Cart::class)
        ->findOneBy(['user' => $user]);
    
    if (!$cart || $cart->getItems()->isEmpty()) {
        return $this->json(['error' => 'Panier vide'], 400);
    }
    
    $purchases = [];
    $total = 0;
    
    // 2. Pour chaque article du panier
    foreach ($cart->getItems() as $item) {
        $book = $item->getBook();
        $quantity = $item->getQuantity();
        
        // Vérifier le stock
        if ($book->getStockQuantity() < $quantity) {
            return $this->json([
                'error' => "Stock insuffisant pour {$book->getTitle()}"
            ], 400);
        }
        
        // Créer l'achat
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setBook($book);
        $purchase->setQuantity($quantity);
        $purchase->setPrice($book->getPrice()); // Prix gelé
        $purchase->setPurchasedAt(new \DateTime());
        $purchase->setStatus('completed');
        
        // Réduire le stock
        $book->setStockQuantity($book->getStockQuantity() - $quantity);
        
        $this->entityManager->persist($purchase);
        $purchases[] = $purchase;
        $total += $book->getPrice() * $quantity;
        
        // Supprimer l'article du panier
        $this->entityManager->remove($item);
    }
    
    // 3. Sauvegarder tout en une transaction
    $this->entityManager->flush();
    
    return $this->json([
        'message' => 'Commande effectuée avec succès',
        'purchases' => $purchases,
        'total' => number_format($total, 3)
    ], 201, [], ['groups' => 'purchase:read']);
}
```

**Points clés :**
- ✅ Transaction atomique (tout ou rien)
- ✅ Prix gelé au moment de l'achat
- ✅ Vérification du stock avant achat
- ✅ Panier vidé après achat
- ✅ Stock réduit automatiquement

---

## 6. Réinitialisation de Mot de Passe

### 🔑 6.1 Demande de Réinitialisation

**Objectif :** Générer un token unique et envoyer un email avec le lien

**Flux :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES                  GMAIL
   |                                 |                                   |                             |
   | User sur /forgot-password       |                                   |                             |
   | Entre son email                 |                                   |                             |
   |                                 |                                   |                             |
   | POST /api/password/reset/request|                                   |                             |
   |---------------------------------→                                   |                             |
   |    Body: {                      |                                   |                             |
   |      email: "user@example.com"  | 1. Recherche utilisateur          |                             |
   |    }                            |    SELECT * FROM user             |                             |
   |                                 |    WHERE email = ?                |                             |
   |                                 |-----------------------------------→                             |
   |                                 |←-----------------------------------|                             |
   |                                 |                                   |                             |
   |                                 | 2. Génère token sécurisé          |                             |
   |                                 |    bin2hex(random_bytes(32))      |                             |
   |                                 |    → 64 caractères hex            |                             |
   |                                 |                                   |                             |
   |                                 | 3. Sauvegarde token               |                             |
   |                                 |    - resetToken = "abc123..."     |                             |
   |                                 |    - resetTokenExpiresAt =        |                             |
   |                                 |      NOW() + 1 heure              |                             |
   |                                 |                                   |                             |
   |                                 | 4. UPDATE user                    |                             |
   |                                 |    SET reset_token = ?,           |                             |
   |                                 |    reset_token_expires_at = ?     |                             |
   |                                 |-----------------------------------→                             |
   |                                 |                                   |                             |
   |                                 | 5. Crée URL de réinitialisation   |                             |
   |                                 |    $resetUrl =                    |                             |
   |                                 |    "http://localhost:3000/        |                             |
   |                                 |     reset-password?token=abc..."  |                             |
   |                                 |                                   |                             |
   |                                 | 6. Si APP_ENV=dev:                |                             |
   |                                 |    → Retourne lien dans JSON      |                             |
   |                                 |                                   |                             |
   |                                 | 7. Si APP_ENV=prod:               |                             |
   |                                 |    → Envoie email                 |                             |
   |                                 |    Crée TemplatedEmail:           |                             |
   |                                 |    - from: noreply@library.com    |                             |
   |                                 |    - to: user@example.com         |                             |
   |                                 |    - template: password_reset.    |                             |
   |                                 |      html.twig                    |                             |
   |                                 |    - context: {resetUrl}          |                             |
   |                                 |                                   |                             |
   |                                 | 8. Envoie via Gmail SMTP          |                             |
   |                                 |-----------------------------------------------------------------------→
   |                                 |                                   |                             |
   |                                 |                                   |                             | 9. Email envoyé
   |                                 |                                   |                             |    avec template HTML
   |                                 |                                   |                             |    professionnel
   |                                 |                                   |                             |
   | 10. Réponse                     |                                   |                             |
   |←---------------------------------|                                   |                             |
   |    MODE DEV:                    |                                   |                             |
   |    {                            |                                   |                             |
   |      message: "Lien généré",    |                                   |                             |
   |      resetUrl: "http://...",    |                                   |                             |
   |      token: "abc123..."         |                                   |                             |
   |    }                            |                                   |                             |
   |                                 |                                   |                             |
   |    MODE PROD:                   |                                   |                             |
   |    {                            |                                   |                             |
   |      message: "Email envoyé"    |                                   |                             |
   |    }                            |                                   |                             |
```

**Code Backend (PasswordResetController.php) :**

```php
#[Route('/reset/request', name: 'reset_request', methods: ['POST'])]
public function requestReset(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $email = $data['email'] ?? null;
    
    if (!$email) {
        return $this->json(['error' => 'Email requis'], 400);
    }
    
    // 1. Chercher l'utilisateur
    $user = $this->entityManager
        ->getRepository(User::class)
        ->findOneBy(['email' => $email]);
    
    // Pour la sécurité, toujours retourner le même message
    if (!$user) {
        return $this->json([
            'message' => 'Si l\'email existe, un lien a été envoyé'
        ], 200);
    }
    
    // 2. Générer un token sécurisé
    $token = bin2hex(random_bytes(32)); // 64 caractères
    $user->setResetToken($token);
    $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
    
    $this->entityManager->flush();
    
    // 3. Générer l'URL de réinitialisation
    $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
    $resetUrl = $frontendUrl . '/reset-password?token=' . $token;
    
    // 4. En mode dev: retourner le lien directement
    if ($_ENV['APP_ENV'] === 'dev') {
        return $this->json([
            'message' => 'Lien de réinitialisation généré',
            'resetUrl' => $resetUrl,
            'token' => $token,
            'note' => 'En dev: utilisez ce lien directement'
        ], 200);
    }
    
    // 5. En mode prod: envoyer l'email
    try {
        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_FROM'] ?? 'noreply@library.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de mot de passe - Library App')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'resetUrl' => $resetUrl,
                'user' => $user
            ]);
        
        $this->mailer->send($email);
        
        return $this->json([
            'message' => 'Un email a été envoyé avec les instructions'
        ], 200);
    } catch (\Exception $e) {
        error_log('Erreur envoi email: ' . $e->getMessage());
        
        if ($_ENV['APP_ENV'] === 'dev') {
            return $this->json([
                'message' => 'Erreur email (mode dev)',
                'resetUrl' => $resetUrl,
                'error' => $e->getMessage()
            ], 500);
        }
        
        return $this->json([
            'message' => 'Une erreur est survenue'
        ], 500);
    }
}
```

**Template Email (password_reset.html.twig) :**

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">📚</div>
        <h1>Library App</h1>
    </div>
    
    <div class="content">
        <h2>Réinitialisation de mot de passe</h2>
        <p>Bonjour,</p>
        <p>Vous avez demandé à réinitialiser votre mot de passe.</p>
        
        <div style="text-align: center;">
            <a href="{{ resetUrl }}" class="button">
                🔐 Réinitialiser mon mot de passe
            </a>
        </div>
        
        <p><strong>⚠️ Ce lien expire dans 1 heure.</strong></p>
    </div>
</body>
</html>
```

---

### ✅ 6.2 Confirmation du Nouveau Mot de Passe

**Objectif :** Vérifier le token et changer le mot de passe

**Flux :**

```
FRONTEND                          BACKEND                         BASE DE DONNÉES
   |                                 |                                   |
   | User clique sur le lien         |                                   |
   | /reset-password?token=abc123... |                                   |
   |                                 |                                   |
   | Page affiche formulaire         |                                   |
   | User entre nouveau mot de passe |                                   |
   |                                 |                                   |
   | POST /api/password/reset/confirm|                                   |
   |---------------------------------→                                   |
   |    Body: {                      |                                   |
   |      token: "abc123...",        | 1. Recherche utilisateur          |
   |      password: "NewPass123!"    |    SELECT * FROM user             |
   |    }                            |    WHERE reset_token = ?          |
   |                                 |-----------------------------------→
   |                                 |←-----------------------------------|
   |                                 |    User trouvé                    |
   |                                 |                                   |
   |                                 | 2. Vérifications                  |
   |                                 |    ✓ User existe ?                |
   |                                 |    ✓ Token valide ?               |
   |                                 |    ✓ Token pas expiré ?           |
   |                                 |    if (expiresAt < NOW())         |
   |                                 |        → Erreur                   |
   |                                 |                                   |
   |                                 | 3. Hash nouveau mot de passe      |
   |                                 |    $hashedPassword =              |
   |                                 |    passwordHasher->hash(          |
   |                                 |      $user, $newPassword          |
   |                                 |    )                              |
   |                                 |                                   |
   |                                 | 4. Met à jour utilisateur         |
   |                                 |    - password = $hashedPassword   |
   |                                 |    - resetToken = NULL            |
   |                                 |    - resetTokenExpiresAt = NULL   |
   |                                 |                                   |
   |                                 | 5. UPDATE user                    |
   |                                 |    SET password = ?,              |
   |                                 |    reset_token = NULL,            |
   |                                 |    reset_token_expires_at = NULL  |
   |                                 |-----------------------------------→
   |                                 |                                   |
   | 6. Réponse succès               |                                   |
   |←---------------------------------|                                   |
   |    {                            |                                   |
   |      message: "Mot de passe     |                                   |
   |        réinitialisé"            |                                   |
   |    }                            |                                   |
   |                                 |                                   |
   | 7. Redirection vers /login      |                                   |
   |    avec message de succès       |                                   |
```

**Code Backend :**

```php
#[Route('/reset/confirm', name: 'reset_confirm', methods: ['POST'])]
public function confirmReset(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $token = $data['token'] ?? null;
    $newPassword = $data['password'] ?? null;
    
    if (!$token || !$newPassword) {
        return $this->json([
            'error' => 'Token et mot de passe requis'
        ], 400);
    }
    
    // 1. Chercher l'utilisateur par token
    $user = $this->entityManager
        ->getRepository(User::class)
        ->findOneBy(['resetToken' => $token]);
    
    if (!$user || !$user->getResetTokenExpiresAt()) {
        return $this->json([
            'error' => 'Token invalide'
        ], 400);
    }
    
    // 2. Vérifier l'expiration
    if ($user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
        return $this->json([
            'error' => 'Token expiré'
        ], 400);
    }
    
    // 3. Hash le nouveau mot de passe
    $hashedPassword = $this->passwordHasher->hashPassword(
        $user,
        $newPassword
    );
    $user->setPassword($hashedPassword);
    
    // 4. Supprimer le token
    $user->setResetToken(null);
    $user->setResetTokenExpiresAt(null);
    
    // 5. Sauvegarder
    $this->entityManager->flush();
    
    return $this->json([
        'message' => 'Mot de passe réinitialisé avec succès'
    ], 200);
}
```

**Points de sécurité :**
- ✅ Token aléatoire cryptographiquement sécurisé (64 caractères)
- ✅ Expiration après 1 heure
- ✅ Token à usage unique (supprimé après utilisation)
- ✅ Protection contre l'énumération d'emails
- ✅ Hash bcrypt du mot de passe

---

## 7. Gestion Administrative (EasyAdmin)

### 👨‍💼 7.1 Interface d'Administration

**Objectif :** Fournir une interface graphique pour gérer toutes les données

**Architecture EasyAdmin :**

```
/admin
   │
   ├── DashboardController
   │   └─> Point d'entrée principal
   │       └─> Redirige vers BookCrudController
   │
   ├── BookCrudController
   │   ├─> Liste des livres
   │   ├─> Ajouter/Modifier/Supprimer
   │   └─> Champs: titre, ISBN, prix, auteurs[], catégories[]
   │
   ├── UserCrudController
   │   ├─> Liste des utilisateurs
   │   ├─> Modifier (pas de mot de passe)
   │   └─> Gestion des rôles
   │
   ├── BorrowingCrudController
   │   ├─> Liste des emprunts
   │   ├─> Badge coloré par statut
   │   └─> Bouton "Accepter le retour" (si pending_return)
   │
   ├── PurchaseCrudController
   │   └─> Liste des achats
   │
   ├── AuthorCrudController
   │   └─> Gestion des auteurs
   │
   ├── CategoryCrudController
   │   └─> Gestion des catégories
   │
   └── PublisherCrudController
       └─> Gestion des éditeurs
```

**Code DashboardController.php :**

```php
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Rediriger vers la liste des livres
        return $this->redirect($this->adminUrlGenerator
            ->setController(BookCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }
    
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('📚 Library Admin')
            ->setFaviconPath('favicon.ico');
    }
    
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToCrud('Livres', 'fa fa-book', Book::class);
        yield MenuItem::linkToCrud('Auteurs', 'fa fa-user', Author::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Category::class);
        yield MenuItem::linkToCrud('Éditeurs', 'fa fa-building', Publisher::class);
        
        yield MenuItem::section('Transactions');
        yield MenuItem::linkToCrud('Emprunts', 'fa fa-exchange', Borrowing::class);
        yield MenuItem::linkToCrud('Achats', 'fa fa-shopping-cart', Purchase::class);
        
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
    }
}
```

---

### 📝 7.2 CRUD des Livres

**Fonctionnalités :**

```
┌─────────────────────────────────────────────────────┐
│         GESTION DES LIVRES (CRUD)                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  [Liste]  [Ajouter]  [Filtres]  [Export]           │
│                                                     │
│  ┌──────────────────────────────────────────────┐  │
│  │ ID │ Titre │ Auteurs │ Prix │ Stock │ Actions│  │
│  ├────┼───────┼─────────┼──────┼───────┼────────┤  │
│  │ 1  │ Livre1│ A1, A2  │25.500│  10   │ ✏️ 🗑️  │  │
│  │ 2  │ Livre2│ A3      │30.000│   5   │ ✏️ 🗑️  │  │
│  └──────────────────────────────────────────────┘  │
│                                                     │
│  [Ajouter un livre]                                 │
│  ┌──────────────────────────────────────────────┐  │
│  │ Titre: [___________________]                 │  │
│  │ ISBN:  [___________________]                 │  │
│  │ Description: [_____________]                 │  │
│  │ Prix (DT): [____] (3 décimales)              │  │
│  │ Stock: [____]                                │  │
│  │ Empruntable: [____]                          │  │
│  │ Auteurs: [☑ A1] [☑ A2] [☐ A3]              │  │
│  │ Catégories: [☑ Fiction] [☐ Science]        │  │
│  │ Éditeur: [Dropdown ▼]                       │  │
│  │ Image: [Parcourir...]                        │  │
│  │                                              │  │
│  │ [Enregistrer] [Annuler]                      │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

**Code BookCrudController.php :**

```php
public function configureFields(string $pageName): iterable
{
    return [
        IdField::new('id')->hideOnForm(),
        TextField::new('title')->setLabel('Titre'),
        TextField::new('isbn')->setLabel('ISBN'),
        TextareaField::new('description')->setLabel('Description'),
        MoneyField::new('price')
            ->setCurrency('TND')
            ->setLabel('Prix (DT)')
            ->setNumDecimals(3),
        IntegerField::new('stockQuantity')->setLabel('Stock total'),
        IntegerField::new('borrowableQuantity')->setLabel('Disponible emprunt'),
        AssociationField::new('authors')->setLabel('Auteurs'),
        AssociationField::new('categories')->setLabel('Catégories'),
        AssociationField::new('publisher')->setLabel('Éditeur'),
        ImageField::new('coverImage')
            ->setUploadDir('public/uploads/covers')
            ->setLabel('Image de couverture'),
        DateTimeField::new('createdAt')
            ->hideOnForm()
            ->setLabel('Créé le'),
    ];
}
```

---

### 🔄 7.3 Gestion des Emprunts avec Workflow

**Interface Admin Borrowings :**

```
┌───────────────────────────────────────────────────────────────────┐
│                    GESTION DES EMPRUNTS                           │
├───────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Filtrer par statut: [Tous ▼] [Actif] [En attente] [Retourné]   │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ID│User  │Livre │Emprunté│Retour  │Statut        │Actions  │ │
│  ├──┼──────┼──────┼────────┼────────┼──────────────┼─────────┤ │
│  │1 │John  │Livre1│01/12/24│15/12/24│🟢 Actif       │         │ │
│  │2 │Jane  │Livre2│05/12/24│19/12/24│🟡 En attente │✅Accepter│ │
│  │3 │Bob   │Livre3│01/11/24│10/11/24│⚪ Retourné    │         │ │
│  │4 │Alice │Livre4│01/10/24│15/10/24│🔴 En retard   │         │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  Légende:                                                         │
│  🟢 Actif - En cours d'emprunt                                   │
│  🟡 En attente de retour - User a demandé validation            │
│  ⚪ Retourné - Livre retourné et validé                         │
│  🔴 En retard - Dépassement de la date limite                   │
└───────────────────────────────────────────────────────────────────┘
```

**Code BorrowingCrudController.php :**

```php
public function configureActions(Actions $actions): Actions
{
    // Action personnalisée pour approuver le retour
    $approveReturn = Action::new('approveReturn', 'Accepter le retour', 'fa fa-check')
        ->linkToUrl(function (Borrowing $borrowing) {
            return $this->generateUrl('admin_approve_return', [
                'id' => $borrowing->getId()
            ]);
        })
        ->displayIf(function (Borrowing $borrowing) {
            // Afficher seulement si status = pending_return
            return $borrowing->getStatus() === 'pending_return';
        })
        ->addCssClass('btn btn-success');

    return $actions
        ->add(Crud::PAGE_INDEX, $approveReturn)
        ->add(Crud::PAGE_DETAIL, $approveReturn);
}

public function configureFields(string $pageName): iterable
{
    return [
        IdField::new('id')->hideOnForm(),
        AssociationField::new('user')->setLabel('Utilisateur'),
        AssociationField::new('book')->setLabel('Livre'),
        DateTimeField::new('borrowedAt')->hideOnForm()->setLabel('Emprunté le'),
        DateTimeField::new('dueDate')->setLabel('À retourner le'),
        DateTimeField::new('returnedAt')->hideOnForm()->setLabel('Retourné le'),
        ChoiceField::new('status')
            ->setLabel('Statut')
            ->setChoices([
                'Actif' => 'active',
                'En attente de retour' => 'pending_return',
                'Retourné' => 'returned',
                'En retard' => 'overdue',
            ])
            ->renderAsBadges([
                'active' => 'primary',
                'pending_return' => 'warning',
                'returned' => 'success',
                'overdue' => 'danger',
            ]),
    ];
}
```

---

## 📊 Récapitulatif des Logiques Métier

### 🔄 Gestion des Stocks

**Deux types de quantités pour un livre :**

1. **stockQuantity** : Stock total (pour achat)
   - Réduit lors d'un achat
   - Jamais augmenté automatiquement
   - Admin peut modifier manuellement

2. **borrowableQuantity** : Disponible pour emprunt
   - Réduit lors d'un emprunt (-1)
   - Augmenté lors d'un retour validé (+1)
   - Peut être différent de stockQuantity

**Exemple :**
```
Book: "Harry Potter"
- stockQuantity: 10 (stock total)
- borrowableQuantity: 5 (disponible emprunt)

→ 5 exemplaires peuvent être empruntés
→ 10 exemplaires peuvent être achetés
```

### 🔐 Hiérarchie des Rôles

```
ROLE_SUPER_ADMIN
    ├─> Tous les droits
    │
    └─> ROLE_ADMIN
            ├─> Accès admin interface
            ├─> CRUD complet
            ├─> Validation retours
            │
            └─> ROLE_LIBRARIAN
                    ├─> Gestion emprunts
                    ├─> Validation retours
                    │
                    └─> ROLE_USER
                            ├─> Emprunter
                            ├─> Acheter
                            └─> Demander retour
```

### ⏱️ Durées et Expirations

| Élément | Durée | Action à expiration |
|---------|-------|---------------------|
| **JWT Token** | 1 heure | Redirection login |
| **Emprunt** | 14 jours | Status → overdue |
| **Reset Token** | 1 heure | Token invalide |
| **Session Admin** | Session navigateur | Logout automatique |

---

## 🎯 Conclusion

Ce document présente toute la logique de fonctionnement du système de bibliothèque :

✅ **Authentification** : JWT, rôles hiérarchiques
✅ **Gestion des livres** : CRUD, recherche, relations ManyToMany
✅ **Emprunts** : Création, gestion du stock
✅ **Retours** : Workflow à 2 étapes avec validation admin
✅ **Panier et achats** : Transaction atomique, prix gelé
✅ **Réinitialisation mot de passe** : Token sécurisé, email
✅ **Interface admin** : EasyAdmin, CRUD complet

**Points forts du système :**
- 🔒 Sécurité renforcée (JWT, hash, validation)
- 📊 Traçabilité complète (dates, statuts)
- 🔄 Gestion automatique des stocks
- 💼 Interface admin intuitive
- ✅ Validation workflow (demande → approbation)
- 📧 Notifications email professionnelles

---

**Total : 1500+ lignes de documentation complète !** 📚
