<?php

namespace App\Modules\Portal\Filament\Resources\PermissionResource\Pages;

use App\Modules\Portal\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function afterCreate(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
