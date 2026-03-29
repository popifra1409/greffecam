<?php

namespace App\Modules\Portal\Filament\Resources\UserResource\Pages;

use App\Modules\Portal\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
