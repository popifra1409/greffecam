<?php

namespace App\Modules\Portal\Filament\Resources\RoleResource\Pages;

use App\Modules\Portal\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        // ✅ Vider le cache pour que le nouveau rôle soit immédiatement actif
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rôle créé et appliqué immédiatement';
    }
}
