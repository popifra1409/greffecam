<?php

namespace App\Modules\DecisionRecours\Filament\Resources\MatiereResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\MatiereResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMatieres extends ListRecords
{
    protected static string $resource = MatiereResource::class;

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
