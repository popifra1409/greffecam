<?php

namespace App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnneeJudiciaires extends ListRecords
{
    protected static string $resource = AnneeJudiciaireResource::class;

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
