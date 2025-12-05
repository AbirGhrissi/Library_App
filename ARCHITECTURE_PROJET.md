# 📚 Architecture Complète du Projet - Bibliothèque en Ligne

## 🎯 Vue d'Ensemble

Ce projet est une **application web de gestion de bibliothèque** avec :
- **Backend** : Symfony 7 (PHP) - API REST
- **Frontend** : Next.js 14 (React/TypeScript)
- **Base de données** : MySQL
- **Authentification** : JWT (JSON Web Tokens)

---

## 🏗️ Architecture Globale

```
┌─────────────────────────────────────────────────────────────┐
│                      UTILISATEURS                            │
│                  (Navigateur Web)                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ├──> Interface Admin (http://127.0.0.1:8000/admin)
                     │    └─> EasyAdmin (Symfony)
                     │
                     └──> Application Frontend (http://localhost:3000)
                          └─> Next.js + React + TypeScript
                               │
                               │ Requêtes HTTP/REST
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND API REST                          │
│                  (Symfony 7 - PHP 8.3)                       │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ Controllers  │  │   Services   │  │   Security   │     │
│  │    (API)     │─>│   (Logic)    │─>│    (JWT)     │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│         │                                                    │
│         ▼                                                    │
│  ┌──────────────┐  ┌──────────────┐                        │
│  │   Entities   │─>│  Repositories│                        │
│  │  (Doctrine)  │  │    (ORM)     │                        │
│  └──────────────┘  └──────────────┘                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              BASE DE DONNÉES MYSQL                           │
│  Tables: user, book, author, category, publisher,           │
│          borrowing, purchase, cart, cart_item               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Structure du Projet

```
biblio-app/
│
├── backend/                          # Application Symfony (API)
│   ├── src/
│   │   ├── Controller/              # Contrôleurs API et Admin
│   │   │   ├── Admin/              # Interface d'administration
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── BookCrudController.php
│   │   │   │   ├── UserCrudController.php
│   │   │   │   ├── BorrowingCrudController.php
│   │   │   │   └── ...
│   │   │   ├── AuthController.php   # Login/Register
│   │   │   ├── BookSearchController.php
│   │   │   ├── BorrowingController.php
│   │   │   ├── CartController.php
│   │   │   ├── PasswordResetController.php
│   │   │   └── UserBooksController.php
│   │   │
│   │   ├── Entity/                  # Modèles de données (ORM)
│   │   │   ├── User.php            # Utilisateurs
│   │   │   ├── Book.php            # Livres
│   │   │   ├── Author.php          # Auteurs
│   │   │   ├── Category.php        # Catégories
│   │   │   ├── Publisher.php       # Éditeurs
│   │   │   ├── Borrowing.php       # Emprunts
│   │   │   ├── Purchase.php        # Achats
│   │   │   ├── Cart.php            # Panier
│   │   │   └── CartItem.php        # Articles du panier
│   │   │
│   │   ├── Repository/              # Accès aux données
│   │   │   ├── UserRepository.php
│   │   │   ├── BookRepository.php
│   │   │   └── ...
│   │   │
│   │   ├── Security/                # Authentification
│   │   │   └── AdminAuthenticator.php
│   │   │
│   │   ├── EventSubscriber/         # Événements Doctrine
│   │   │   └── UserPasswordHashSubscriber.php
│   │   │
│   │   └── Service/                 # Logique métier
│   │       └── PermissionService.php 
│   │
│   ├── config/                      # Configuration
│   │   ├── packages/
│   │   │   ├── security.yaml       # Sécurité et authentification
│   │   │   ├── doctrine.yaml       # Configuration base de données
│   │   │   ├── api_platform.yaml   # Configuration API
│   │   │   └── ...
│   │   └── routes.yaml             # Routes de l'application
│   │
│   ├── migrations/                  # Migrations de base de données
│   ├── templates/                   # Templates Twig
│   │   └── emails/
│   │       └── password_reset.html.twig
│   └── public/                      # Point d'entrée web
│       └── index.php
│
└── frontend/                        # Application Next.js
    ├── app/                         # Pages (App Router)
    │   ├── page.tsx                # Page d'accueil
    │   ├── login/
    │   ├── register/
    │   ├── books/
    │   ├── cart/
    │   ├── my-books/
    │   └── ...
    │
    ├── components/                  # Composants React
    │   ├── auth/
    │   ├── books/
    │   └── layout/
    │
    └── lib/                         # Utilitaires
        ├── api.ts                  # Fonctions API
        ├── AuthContext.tsx         # Contexte d'authentification
        └── CartContext.tsx         # Contexte du panier
```

---

## 🔐 Système d'Authentification

### 1. JWT (JSON Web Tokens)

```
┌─────────────┐                    ┌─────────────┐
│   Client    │                    │   Backend   │
└──────┬──────┘                    └──────┬──────┘
       │                                  │
       │ POST /api/login                  │
       │ {email, password}                │
       ├─────────────────────────────────>│
       │                                  │
       │                                  │ 1. Vérifie credentials
       │                                  │ 2. Hash password
       │                                  │ 3. Génère JWT
       │                                  │
       │ {token: "eyJhbG..."}             │
       │<─────────────────────────────────┤
       │                                  │
       │ Stocke token dans localStorage   │
       │                                  │
       │ GET /api/books                   │
       │ Header: Authorization: Bearer... │
       ├─────────────────────────────────>│
       │                                  │
       │                                  │ Vérifie JWT
       │                                  │
       │ {books: [...]}                   │
       │<─────────────────────────────────┤
```

### 2. Hiérarchie des Rôles

```
ROLE_SUPER_ADMIN (Super Administrateur)
    │
    ├─> Tous les droits
    │
    └─> ROLE_ADMIN (Administrateur)
            │
            ├─> Gestion complète (sauf super admin)
            │
            └─> ROLE_LIBRARIAN (Bibliothécaire)
                    │
                    ├─> Gestion des emprunts et retours
                    │
                    └─> ROLE_USER (Utilisateur)
                            │
                            └─> Emprunter, acheter, panier
```

### 3. Configuration dans `security.yaml`

```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
    
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    
    firewalls:
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: admin_login
                check_path: admin_login
                default_target_path: admin
            logout:
                path: app_logout
    
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/api/borrowings, roles: ROLE_USER }
```

---

## 📊 Base de Données - Schéma

### Relations entre les entités

```
┌──────────────┐
│     USER     │
│──────────────│
│ id           │
│ email        │◄──────────┐
│ password     │           │
│ firstName    │           │
│ lastName     │           │
│ roles[]      │           │
└──────┬───────┘           │
       │                   │
       │ 1                 │ N
       │                   │
       ▼ N                 │
┌──────────────┐           │
│  BORROWING   │           │
│──────────────│           │
│ id           │           │
│ user_id      │───────────┘
│ book_id      │───────┐
│ borrowedAt   │       │
│ dueDate      │       │
│ returnedAt   │       │
│ status       │       │
└──────────────┘       │
                       │
       ┌───────────────┘
       │ N
       ▼
┌──────────────┐         ┌──────────────┐
│     BOOK     │    N    │    AUTHOR    │
│──────────────│◄────────┤──────────────│
│ id           │  M:N    │ id           │
│ title        │         │ name         │
│ isbn         │         │ bio          │
│ price        │         └──────────────┘
│ stockQuantity│
│ borrowable   │         ┌──────────────┐
│  Quantity    │    N    │   CATEGORY   │
└──────┬───────┘◄────────┤──────────────│
       │            M:N  │ id           │
       │                 │ name         │
       │                 └──────────────┘
       │
       │ 1
       ▼ N
┌──────────────┐
│  CART_ITEM   │
│──────────────│
│ id           │
│ cart_id      │───┐
│ book_id      │   │
│ quantity     │   │
└──────────────┘   │
                   │ N
       ┌───────────┘
       │ 1
       ▼
┌──────────────┐
│     CART     │
│──────────────│
│ id           │
│ user_id      │
└──────────────┘
```

---

## 🔄 Flux de Données Principaux

### 1. Emprunt d'un Livre

```
1. Utilisateur clique sur "Emprunter"
   └─> Frontend: POST /api/borrowings
       └─> Body: {bookId: 1}

2. Backend: BorrowingController::create()
   ├─> Vérifie authentification (JWT)
   ├─> Vérifie disponibilité du livre
   ├─> Crée un Borrowing
   │   ├─> status: "active"
   │   ├─> dueDate: +14 jours
   │   └─> borrowedAt: maintenant
   ├─> Réduit borrowableQuantity de 1
   └─> Retourne {borrowing}

3. Frontend affiche le message de succès
```

### 2. Retour d'un Livre

```
1. Utilisateur: "Demander le retour"
   └─> POST /api/borrowings/{id}/request-return

2. Backend change status en "pending_return"

3. Admin voit la demande (badge jaune)

4. Admin: "Accepter le retour"
   └─> GET /admin/approve-return/{id}

5. Backend:
   ├─> Change status en "returned"
   ├─> Définit returnedAt
   └─> Augmente borrowableQuantity de 1

6. Frontend affiche "Retourné" (badge gris)
```

### 3. Ajout au Panier et Achat

```
1. Utilisateur: "Ajouter au panier"
   └─> POST /api/cart/add
       └─> {bookId: 1, quantity: 2}

2. Backend: CartController
   ├─> Trouve/Crée le panier de l'utilisateur
   ├─> Ajoute CartItem
   └─> Retourne le panier complet

3. Utilisateur: "Commander"
   └─> POST /api/cart/checkout

4. Backend:
   ├─> Crée un Purchase pour chaque item
   ├─> Réduit stockQuantity
   ├─> Vide le panier
   └─> Retourne {purchases}
```

---

## 🎨 Frontend - Architecture React

### 1. Contextes (State Management)

```typescript
// AuthContext.tsx
export const AuthContext = createContext({
  user: null,
  login: (email, password) => {},
  logout: () => {},
  isAuthenticated: false,
});

// CartContext.tsx
export const CartContext = createContext({
  cart: null,
  addToCart: (bookId, quantity) => {},
  removeFromCart: (itemId) => {},
  checkout: () => {},
});
```

### 2. Structure des Pages

```
app/
├── layout.tsx              # Layout principal avec Navbar
├── page.tsx                # Page d'accueil
├── login/page.tsx          # Connexion
├── register/page.tsx       # Inscription
├── books/
│   ├── page.tsx           # Liste des livres
│   └── [id]/page.tsx      # Détails d'un livre
├── cart/page.tsx          # Panier
├── my-books/page.tsx      # Mes emprunts/achats
└── forgot-password/        # Réinitialisation mot de passe
    └── page.tsx
```

### 3. Composants Réutilisables

```typescript
// components/books/BookCard.tsx
export default function BookCard({ book }) {
  return (
    <div className="book-card">
      <h3>{book.title}</h3>
      <p>{book.authors.map(a => a.name).join(', ')}</p>
      <p>{book.price.toFixed(3)} DT</p>
      <button onClick={() => addToCart(book.id)}>
        Ajouter au panier
      </button>
    </div>
  );
}
```

---

## 🔧 Backend - Logique Métier

### 1. Contrôleurs API

**Exemple: BorrowingController**

```php
#[Route('/api/borrowings', name: 'api_borrowings_')]
class BorrowingController extends AbstractController
{
    // Créer un emprunt
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // 1. Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // 2. Parser les données JSON
        $data = json_decode($request->getContent(), true);
        
        // 3. Valider et créer l'emprunt
        $borrowing = new Borrowing();
        $borrowing->setUser($user);
        $borrowing->setBook($book);
        
        // 4. Sauvegarder en base
        $this->entityManager->persist($borrowing);
        $this->entityManager->flush();
        
        // 5. Retourner la réponse JSON
        return $this->json($borrowing);
    }
}
```

### 2. Entités Doctrine

**Exemple: Book**

```php
#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['book:read']]
)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:read'])]
    private ?string $title = null;

    // Relations ManyToMany
    #[ORM\ManyToMany(targetEntity: Author::class)]
    private Collection $authors;

    #[ORM\ManyToMany(targetEntity: Category::class)]
    private Collection $categories;
}
```

### 3. EventSubscriber (Hooks)

```php
class UserPasswordHashSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,  // Avant insertion
            Events::preUpdate,   // Avant mise à jour
        ];
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof User) {
            $this->hashPassword($entity);
        }
    }
}
```

---

## 📧 Système d'Email

### Configuration Gmail

```env
# .env.local
MAILER_DSN=gmail://email@gmail.com:mot-de-passe-app@default
MAILER_FROM=email@gmail.com
FRONTEND_URL=http://localhost:3000
```

### Envoi d'Email

```php
$email = (new TemplatedEmail())
    ->from($_ENV['MAILER_FROM'])
    ->to($user->getEmail())
    ->subject('Réinitialisation de mot de passe')
    ->htmlTemplate('emails/password_reset.html.twig')
    ->context(['resetUrl' => $resetUrl]);

$this->mailer->send($email);
```

---

## 🛠️ Commandes Utiles

### Backend (Symfony)

```bash
# Démarrer le serveur
symfony server:start -d

# Créer une entité
php bin/console make:entity Book

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Créer un admin
php bin/console app:create-admin
```

### Frontend (Next.js)

```bash
# Installer les dépendances
npm install

# Démarrer en mode développement
npm run dev

# Build pour production
npm run build

# Démarrer en production
npm start
```

---

## 🔍 Points Clés de l'Architecture

### 1. Séparation des Préoccupations

- **Backend** : API REST pure (pas de HTML)
- **Frontend** : Interface utilisateur pure
- **Communication** : JSON via HTTP

### 2. Sécurité

- **JWT** pour l'authentification
- **CORS** configuré
- **Validation** des données
- **Hash** des mots de passe (bcrypt)
- **CSRF** protection sur les formulaires admin

### 3. Scalabilité

- **Stateless** : JWT permet la scalabilité horizontale
- **Cache** : Doctrine cache, HTTP cache
- **API REST** : Peut servir plusieurs clients (web, mobile)

### 4. Maintenabilité

- **Code organisé** : MVC pattern
- **Typage** : PHP 8.3 types, TypeScript
- **ORM** : Doctrine (pas de SQL brut)
- **DRY** : Composants réutilisables

---

Voulez-vous que je détaille un aspect particulier de l'architecture ? 😊
