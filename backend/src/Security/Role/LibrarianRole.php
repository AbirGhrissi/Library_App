<?php

namespace App\Security\Role;

/**
 * Rôle pour les bibliothécaires
 * Hérite de UserRole + permissions supplémentaires
 * Permissions: Gérer les emprunts, voir les statistiques
 */
class LibrarianRole extends UserRole
{
    public const ROLE = Role::LIBRARIAN;

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), [
            'borrowing.view_all',      // Voir tous les emprunts
            'borrowing.manage',        // Gérer les emprunts (retours)
            'purchase.view_all',       // Voir tous les achats
            'user.view_all',          // Voir tous les utilisateurs
            'book.stats',             // Voir les statistiques des livres
            'report.generate',        // Générer des rapports
        ]);
    }

    public function getDescription(): string
    {
        return 'Bibliothécaire - Peut gérer les emprunts et voir les statistiques';
    }
}
