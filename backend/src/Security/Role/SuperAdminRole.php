<?php

namespace App\Security\Role;

/**
 * Rôle pour les super administrateurs
 * Hérite de AdminRole + permissions supplémentaires
 * Permissions: Tout, y compris gestion des admins
 */
class SuperAdminRole extends AdminRole
{
    public const ROLE = Role::SUPER_ADMIN;

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), [
            'user.create',           // Créer des utilisateurs
            'user.delete',           // Supprimer des utilisateurs
            'user.manage_roles',     // Gérer les rôles
            'admin.manage',          // Gérer les administrateurs
            'system.config',         // Configurer le système
            'database.backup',       // Sauvegarder la base de données
            'logs.view',             // Voir les logs système
        ]);
    }

    public function getDescription(): string
    {
        return 'Super Administrateur - Accès complet au système';
    }
}
