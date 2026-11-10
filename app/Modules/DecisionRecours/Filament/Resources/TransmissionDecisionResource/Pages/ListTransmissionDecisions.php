<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TransmissionDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TransmissionDecisionResource;
use Filament\Resources\Pages\ListRecords;

class ListTransmissionDecisions extends ListRecords
{
    protected static string $resource = TransmissionDecisionResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canViewAny(), 403);
    }
}
