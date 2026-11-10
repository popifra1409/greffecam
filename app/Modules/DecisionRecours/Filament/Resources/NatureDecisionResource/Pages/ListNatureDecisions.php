<?php

namespace App\Modules\DecisionRecours\Filament\Resources\NatureDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\NatureDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNatureDecisions extends ListRecords
{
    protected static string $resource = NatureDecisionResource::class;

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
