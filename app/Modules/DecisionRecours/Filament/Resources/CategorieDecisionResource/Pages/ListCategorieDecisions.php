<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CategorieDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CategorieDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategorieDecisions extends ListRecords
{
    protected static string $resource = CategorieDecisionResource::class;

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
