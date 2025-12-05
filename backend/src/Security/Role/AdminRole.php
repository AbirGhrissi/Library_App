<?php

namespace App\Security\Role;

/**
 * Rôle pour les administrateurs
 * Hérite de LibrarianRole + permissions supplémentaires
 * Permissions: CRUD complet sur livres, auteurs, éditeurs, catégories
 */
class AdminRole extends LibrarianRole
{
    public const ROLE = Role::ADMIN;

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), [
            'book.create',            // Créer des livres
            'book.edit',              // Modifier des livres
            'book.delete',            // Supprimer des livres
            'author.manage',          // Gérer les auteurs
            'publisher.manage',       // Gérer les éditeurs
            'category.manage',        // Gérer les catégories
            'user.edit',             // Modifier les utilisateurs
            'purchase.manage',        // Gérer les achats
            'admin.access',          // Accéder au panel admin
        ]);
    }

    public function getDescription(): string
    {
        return 'Administrateur - Gestion complète de la bibliothèque';
    }
}
