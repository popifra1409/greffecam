<?php

namespace App\Modules\DecisionRecours\Filament\Resources\JourFerieResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\JourFerieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJourFeries extends ListRecords
{
    protected static string $resource = JourFerieResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canViewAny(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn(): bool => static::getResource()::canCreate()),
        ];
    }
}
