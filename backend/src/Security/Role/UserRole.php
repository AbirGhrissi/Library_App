<?php

namespace App\Security\Role;

/**
 * Rôle de base pour tous les utilisateurs
 * Permissions: Consulter, Emprunter, Acheter
 */
class UserRole
{
    public const ROLE = Role::USER;

    public function getPermissions(): array
    {
        return [
            'book.view',        // Voir les livres
            'book.search',      // Rechercher des livres
            'book.borrow',      // Emprunter des livres
            'book.purchase',    // Acheter des livres
            'cart.manage',      // Gérer son panier
            'profile.view',     // Voir son profil
            'profile.edit',     // Modifier son profil
        ];
    }

    public function getDescription(): string
    {
        return 'Utilisateur standard - Peut consulter, emprunter et acheter des livres';
    }
}
