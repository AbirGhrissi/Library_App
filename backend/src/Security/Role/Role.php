<?php

namespace App\Security\Role;

abstract class Role
{
    public const USER = 'ROLE_USER';
    public const LIBRARIAN = 'ROLE_LIBRARIAN';
    public const ADMIN = 'ROLE_ADMIN';
    public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Retourne tous les rôles disponibles
     */
    public static function all(): array
    {
        return [
            self::USER,
            self::LIBRARIAN,
            self::ADMIN,
            self::SUPER_ADMIN,
        ];
    }

    /**
     * Retourne les rôles avec leurs labels
     */
    public static function getChoices(): array
    {
        return [
            'Utilisateur' => self::USER,
            'Bibliothécaire' => self::LIBRARIAN,
            'Administrateur' => self::ADMIN,
            'Super Administrateur' => self::SUPER_ADMIN,
        ];
    }

    /**
     * Retourne la hiérarchie des rôles
     */
    public static function getHierarchy(): array
    {
        return [
            self::SUPER_ADMIN => [self::ADMIN],
            self::ADMIN => [self::LIBRARIAN],
            self::LIBRARIAN => [self::USER],
        ];
    }
}
