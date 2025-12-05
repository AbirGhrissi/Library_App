# 📁 Fichiers Responsables par Fonctionnalité

## 🔍 1. RECHERCHE DE LIVRES

### Backend (API)
```
backend/src/
├── Controller/
│   └── BookSearchController.php          ← Endpoint /api/books/search
│       └─> search(Request $request)      ← Méthode principale
│
├── Repository/
│   └── BookRepository.php                ← Logique de recherche
│       └─> advancedSearch($criteria)     ← QueryBuilder avec JOIN
│
└── Entity/
    ├── Book.php                          ← Définit relations (authors, categories)
    ├── Author.php                        ← Relation ManyToMany
    └── Category.php                      ← Relation ManyToMany
```

### Frontend (UI)
```
frontend/
├── components/books/
│   └── BookSearch.tsx                    ← Barre de recherche
│       ├─> handleSearch()                ← Appel API
│       └─> useState, useEffect           ← Gestion état
│
├── app/books/
│   └── page.tsx                          ← Page liste des livres
│       └─> Affiche résultats             ← Utilise BookSearch
│
└── lib/
    └── api.ts                            ← Fonctions API réutilisables
        └─> searchBooks(query)            ← Wrapper fetch
```

### Configuration
```
backend/config/routes/
└── annotations.yaml                      ← Routes API Platform
```

---

## 📖 2. EMPRUNT DE LIVRE

### Backend (API)
```
backend/src/
├── Controller/
│   └── BorrowingController.php           ← Endpoint /api/borrowings
│       ├─> create()                      ← POST /api/borrowings
│       ├─> requestReturn()               ← POST /api/borrowings/{id}/request-return
│       └─> approveReturn()               ← POST /api/borrowings/{id}/approve-return
│
├── Entity/
│   ├── Borrowing.php                     ← Table borrowing
│   │   ├─> user (ManyToOne User)        ← Relation
│   │   ├─> book (ManyToOne Book)        ← Relation
│   │   ├─> borrowedAt                   ← Date emprunt
│   │   ├─> dueDate                      ← Date retour
│   │   ├─> returnedAt                   ← Date retour réel
│   │   └─> status                       ← État (active, pending_return, returned)
│   │
│   ├── Book.php                          ← Gestion stock
│   │   ├─> borrowableQuantity           ← Stock empruntable
│   │   └─> stockQuantity                ← Stock total
│   │
│   └── User.php                          ← Utilisateur
│       └─> borrowings (OneToMany)       ← Relation inverse
│
└── Repository/
    └── BorrowingRepository.php           ← Requêtes customs
```

### Frontend (UI)
```
frontend/
├── app/books/[id]/
│   └── page.tsx                          ← Page détail livre
│       └─> Bouton "Emprunter"            ← Appel API
│
├── app/my-books/
│   └── page.tsx                          ← Mes emprunts
│       ├─> Liste des emprunts            ← Affichage
│       └─> Bouton "Demander retour"      ← Workflow retour
│
└── lib/
    └── api.ts                            ← Fonctions API
        ├─> borrowBook(bookId)            ← POST /api/borrowings
        └─> requestReturn(borrowingId)    ← POST retour
```

### Admin (Validation)
```
backend/src/Controller/Admin/
├── BorrowingCrudController.php           ← Interface admin emprunts
│   ├─> configureActions()                ← Ajoute bouton "Accepter"
│   └─> configureFields()                 ← Badges colorés
│
└── ApproveReturnController.php           ← Validation retour
    └─> approveReturn($id)                ← GET /admin/approve-return/{id}
```

### Configuration
```
backend/config/packages/
└── security.yaml                         ← Accès ROLE_USER minimum
```

---

## 🛒 3. PANIER ET ACHATS

### Backend (API)
```
backend/src/
├── Controller/
│   └── CartController.php                ← Endpoint /api/cart
│       ├─> getCart()                     ← GET /api/cart
│       ├─> addToCart()                   ← POST /api/cart/add
│       ├─> updateQuantity()              ← PUT /api/cart/item/{id}
│       ├─> removeFromCart()              ← DELETE /api/cart/item/{id}
│       └─> checkout()                    ← POST /api/cart/checkout
│
├── Entity/
│   ├── Cart.php                          ← Table cart (panier)
│   │   ├─> user (OneToOne User)         ← 1 panier par user
│   │   └─> items (OneToMany CartItem)   ← Articles du panier
│   │
│   ├── CartItem.php                      ← Table cart_item (ligne panier)
│   │   ├─> cart (ManyToOne Cart)        ← Appartient à un panier
│   │   ├─> book (ManyToOne Book)        ← Livre
│   │   └─> quantity                     ← Quantité
│   │
│   ├── Purchase.php                      ← Table purchase (achat finalisé)
│   │   ├─> user (ManyToOne User)        ← Acheteur
│   │   ├─> book (ManyToOne Book)        ← Livre acheté
│   │   ├─> quantity                     ← Quantité
│   │   ├─> price                        ← Prix gelé
│   │   ├─> purchasedAt                  ← Date achat
│   │   └─> status                       ← État
│   │
│   └── User.php
│       ├─> cart (OneToOne Cart)         ← Relation
│       └─> purchases (OneToMany)        ← Historique achats
│
└── Repository/
    ├── CartRepository.php                ← Requêtes panier
    └── PurchaseRepository.php            ← Requêtes achats
```

### Frontend (UI)
```
frontend/
├── app/cart/
│   └── page.tsx                          ← Page panier
│       ├─> Liste items                   ← Affichage
│       ├─> updateQuantity()              ← Modifier quantité
│       ├─> removeItem()                  ← Supprimer
│       └─> checkout()                    ← Commander
│
├── app/my-books/
│   └── page.tsx                          ← Mes achats (onglet)
│       └─> Liste purchases               ← Historique
│
├── components/books/
│   └── BookCard.tsx                      ← Carte livre
│       └─> Bouton "Ajouter au panier"    ← Appel API
│
└── lib/
    ├── api.ts                            ← Fonctions API
    │   ├─> addToCart(bookId, qty)
    │   ├─> updateCartItem(itemId, qty)
    │   └─> checkout()
    │
    └── CartContext.tsx                   ← Context React
        ├─> cart state                    ← État global panier
        ├─> addToCart()                   ← Actions
        ├─> removeFromCart()
        └─> checkout()
```

### Admin (Gestion)
```
backend/src/Controller/Admin/
└── PurchaseCrudController.php            ← Interface admin achats
    └─> Liste tous les achats             ← Visualisation
```

---

## 🔐 4. RESET PASSWORD

### Backend (API + Email)
```
backend/src/
├── Controller/
│   └── PasswordResetController.php       ← Endpoint /api/password
│       ├─> requestReset()                ← POST /api/password/reset/request
│       │   ├─> Génère token              ← bin2hex(random_bytes(32))
│       │   ├─> Sauvegarde en BDD         ← User.resetToken
│       │   └─> Envoie email              ← Mailer
│       │
│       └─> confirmReset()                ← POST /api/password/reset/confirm
│           ├─> Vérifie token             ← findOneBy(['resetToken' => ...])
│           ├─> Vérifie expiration        ← resetTokenExpiresAt
│           ├─> Hash nouveau password     ← PasswordHasher
│           └─> Supprime token            ← resetToken = null
│
├── Entity/
│   └── User.php                          ← Table user
│       ├─> resetToken                    ← Token unique (64 char)
│       └─> resetTokenExpiresAt           ← Date expiration (+1h)
│
├── EventSubscriber/
│   └── UserPasswordHashSubscriber.php    ← Hash automatique password
│       └─> prePersist, preUpdate         ← Events Doctrine
│
└── templates/emails/
    └── password_reset.html.twig          ← Template email HTML
        ├─> Design professionnel          ← Gradient violet/bleu
        ├─> Bouton CTA                    ← Lien réinitialisation
        └─> Variables: {{ resetUrl }}     ← Contexte Twig
```

### Frontend (UI)
```
frontend/
├── app/forgot-password/
│   └── page.tsx                          ← Page "Mot de passe oublié"
│       ├─> Formulaire email              ← Input
│       └─> handleSubmit()                ← POST /api/password/reset/request
│
├── app/reset-password/
│   └── page.tsx                          ← Page réinitialisation
│       ├─> useSearchParams()             ← Récupère token depuis URL
│       ├─> Formulaire nouveau password   ← 2 inputs (password + confirm)
│       └─> handleSubmit()                ← POST /api/password/reset/confirm
│
└── app/login/
    └── page.tsx                          ← Lien "Mot de passe oublié"
        └─> Link to /forgot-password      ← Navigation
```

### Configuration Email
```
backend/
├── .env                                  ← Config développement
│   ├─> MAILER_DSN=smtp://localhost:1025  ← Mailhog
│   └─> FRONTEND_URL=http://localhost:3000
│
├── .env.local                            ← Config production (gitignored)
│   ├─> MAILER_DSN=gmail://...            ← Gmail SMTP
│   ├─> MAILER_FROM=email@gmail.com
│   └─> APP_ENV=prod
│
└── config/packages/
    └── mailer.yaml                       ← Config Symfony Mailer
        └─> dsn: '%env(MAILER_DSN)%'
```

### Dépendances
```
backend/composer.json
├─> symfony/mailer                        ← Envoi emails
├─> symfony/google-mailer                 ← Support Gmail
└─> twig/twig                             ← Templates emails
```

---

## 📊 Résumé par Type de Fichier

### Backend (Symfony)

| Type | Responsabilité | Exemples |
|------|----------------|----------|
| **Controller/** | Points d'entrée API | BookSearchController, BorrowingController, CartController, PasswordResetController |
| **Entity/** | Structure BDD (ORM) | Book, User, Borrowing, Cart, CartItem, Purchase |
| **Repository/** | Requêtes complexes | BookRepository (advancedSearch), BorrowingRepository |
| **Controller/Admin/** | Interface admin | BookCrudController, BorrowingCrudController, PurchaseCrudController |
| **EventSubscriber/** | Hooks Doctrine | UserPasswordHashSubscriber (hash auto password) |
| **templates/** | Templates email | password_reset.html.twig |

### Frontend (Next.js)

| Type | Responsabilité | Exemples |
|------|----------------|----------|
| **app/** | Pages routes | books/, cart/, my-books/, forgot-password/, reset-password/ |
| **components/** | Composants réutilisables | BookSearch, BookCard, BookList |
| **lib/** | Utilitaires | api.ts (fonctions API), CartContext, AuthContext |

### Configuration

| Fichier | Responsabilité |
|---------|----------------|
| **backend/.env** | Variables d'environnement (dev) |
| **backend/.env.local** | Variables d'environnement (prod, gitignored) |
| **backend/config/packages/** | Configuration Symfony (security, mailer, doctrine, api_platform) |
| **backend/config/routes/** | Routes API |
| **frontend/.env.local** | URL API backend |

---

## 🎯 Architecture par Fonctionnalité

### 1. Recherche : 8 fichiers
```
Backend: 3 (Controller + Repository + Entity)
Frontend: 4 (Page + Component + API + Context)
Config: 1 (routes)
```

### 2. Emprunt : 12 fichiers
```
Backend: 5 (Controller + 3 Entities + Repository + Admin)
Frontend: 3 (Pages + API)
Config: 1 (security)
Admin: 2 (BorrowingCrud + ApproveReturn)
```

### 3. Panier : 14 fichiers
```
Backend: 6 (Controller + 4 Entities + 2 Repositories + Admin)
Frontend: 5 (2 Pages + Component + API + Context)
Config: 1 (security)
Admin: 1 (PurchaseCrud)
```

### 4. Reset Password : 10 fichiers
```
Backend: 4 (Controller + Entity + EventSubscriber + Template)
Frontend: 3 (2 Pages + Link)
Config: 3 (.env, .env.local, mailer.yaml)
```

---

## 💡 Comment Naviguer dans le Code

### Pour comprendre une fonctionnalité :

1. **Commencez par le Controller** (point d'entrée API)
   ```
   backend/src/Controller/NomController.php
   ```

2. **Regardez les Entities** (structure des données)
   ```
   backend/src/Entity/Nom.php
   ```

3. **Vérifiez le Repository** (requêtes complexes)
   ```
   backend/src/Repository/NomRepository.php
   ```

4. **Frontend : Page principale**
   ```
   frontend/app/nom/page.tsx
   ```

5. **Frontend : API calls**
   ```
   frontend/lib/api.ts
   ```

### Pour modifier une fonctionnalité :

1. **Backend :** Controller → Entity → Repository
2. **Frontend :** Page → Component → API function
3. **Config :** .env → packages/*.yaml → routes

---

Voulez-vous que je détaille l'architecture d'une fonctionnalité spécifique ? 😊
