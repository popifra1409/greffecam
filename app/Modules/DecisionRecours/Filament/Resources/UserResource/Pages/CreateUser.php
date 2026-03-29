<?php

namespace App\Modules\DecisionRecours\Filament\Resources\UserResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
