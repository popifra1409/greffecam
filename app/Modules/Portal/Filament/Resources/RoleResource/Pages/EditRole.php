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
                ->visible(fn() => !RoleResource::estRoleProtege($this->record)),
        ];
    }

    protected function afterSave(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Rôle mis à jour - Changements appliqués immédiatement à tous les utilisateurs concernés';
    }
}
