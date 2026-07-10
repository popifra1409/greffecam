<?php

namespace App\Modules\Portal\Filament\Resources\RoleResource\Pages;

use App\Modules\Portal\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => !in_array($this->record->name, RoleResource::getRolesProtegees())),
        ];
    }

    protected function afterSave(): void
    {
        // ✅ ÉTAPE CRITIQUE : vider le cache Spatie
        // Sans ça, les utilisateurs ayant ce rôle gardent leurs anciennes permissions
        // jusqu'à expiration naturelle du cache (par défaut 24h)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Rôle mis à jour - Changements appliqués immédiatement à tous les utilisateurs concernés';
    }
}
