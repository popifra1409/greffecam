<?php

namespace App\Traits;

trait HasResourcePermissions
{
    public static function canViewAny(): bool
    {
        return auth()->check() && (
            auth()->user()->can(static::getViewPermission()) ||
            auth()->user()->hasRole(['Super Administrateur', 'Administrateur'])
        );
    }

    public static function canView($record): bool
    {
        return auth()->check() && (
            auth()->user()->can(static::getViewPermission()) ||
            auth()->user()->hasRole(['Super Administrateur', 'Administrateur'])
        );
    }

    public static function canCreate(): bool
    {
        return auth()->check() && (
            auth()->user()->can(static::getCreatePermission()) ||
            auth()->user()->hasRole(['Super Administrateur', 'Administrateur'])
        );
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && (
            auth()->user()->can(static::getEditPermission()) ||
            auth()->user()->hasRole(['Super Administrateur', 'Administrateur'])
        );
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && (
            auth()->user()->can(static::getDeletePermission()) ||
            auth()->user()->hasRole(['Super Administrateur', 'Administrateur'])
        );
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check() && (
            auth()->user()->can(static::getDeletePermission()) ||
            auth()->user()->hasRole(['Super Administrateur', 'Administrateur'])
        );
    }

    // Méthodes à implémenter dans chaque Resource
    abstract protected static function getViewPermission(): string;
    abstract protected static function getCreatePermission(): string;
    abstract protected static function getEditPermission(): string;
    abstract protected static function getDeletePermission(): string;
}