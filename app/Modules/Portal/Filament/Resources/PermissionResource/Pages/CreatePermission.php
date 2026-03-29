<?php

namespace App\Modules\Portal\Filament\Resources\PermissionResource\Pages;

use App\Modules\Portal\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}
