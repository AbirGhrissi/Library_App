<?php

namespace App\Service;

use App\Security\Role\AdminRole;
use App\Security\Role\LibrarianRole;
use App\Security\Role\SuperAdminRole;
use App\Security\Role\UserRole;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionService
{
    /**
     * Retourne toutes les permissions d'un utilisateur en fonction de ses rôles
     */
    public function getUserPermissions(UserInterface $user): array
    {
        $permissions = [];
        
        foreach ($user->getRoles() as $role) {
            $permissions = array_merge($permissions, $this->getRolePermissions($role));
        }
        
        return array_unique($permissions);
    }

    /**
     * Retourne les permissions d'un rôle spécifique
     */
    public function getRolePermissions(string $role): array
    {
        return match($role) {
            'ROLE_USER' => (new UserRole())->getPermissions(),
            'ROLE_LIBRARIAN' => (new LibrarianRole())->getPermissions(),
            'ROLE_ADMIN' => (new AdminRole())->getPermissions(),
            'ROLE_SUPER_ADMIN' => (new SuperAdminRole())->getPermissions(),
            default => [],
        };
    }

    /**
     * Vérifie si un utilisateur a une permission spécifique
     */
    public function hasPermission(UserInterface $user, string $permission): bool
    {
        $userPermissions = $this->getUserPermissions($user);
        return in_array($permission, $userPermissions);
    }

    /**
     * Retourne la description d'un rôle
     */
    public function getRoleDescription(string $role): string
    {
        return match($role) {
            'ROLE_USER' => (new UserRole())->getDescription(),
            'ROLE_LIBRARIAN' => (new LibrarianRole())->getDescription(),
            'ROLE_ADMIN' => (new AdminRole())->getDescription(),
            'ROLE_SUPER_ADMIN' => (new SuperAdminRole())->getDescription(),
            default => 'Rôle inconnu',
        };
    }

    /**
     * Retourne tous les rôles avec leurs permissions
     */
    public function getAllRolesWithPermissions(): array
    {
        return [
            'ROLE_USER' => [
                'description' => (new UserRole())->getDescription(),
                'permissions' => (new UserRole())->getPermissions(),
            ],
            'ROLE_LIBRARIAN' => [
                'description' => (new LibrarianRole())->getDescription(),
                'permissions' => (new LibrarianRole())->getPermissions(),
            ],
            'ROLE_ADMIN' => [
                'description' => (new AdminRole())->getDescription(),
                'permissions' => (new AdminRole())->getPermissions(),
            ],
            'ROLE_SUPER_ADMIN' => [
                'description' => (new SuperAdminRole())->getDescription(),
                'permissions' => (new SuperAdminRole())->getPermissions(),
            ],
        ];
    }
}
