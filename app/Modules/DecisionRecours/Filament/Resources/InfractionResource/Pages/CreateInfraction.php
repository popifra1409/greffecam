<?php

namespace App\Modules\DecisionRecours\Filament\Resources\InfractionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\InfractionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInfraction extends CreateRecord
{
    protected static string $resource = InfractionResource::class;
}
