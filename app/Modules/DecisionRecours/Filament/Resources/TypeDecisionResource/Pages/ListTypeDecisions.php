<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TypeDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TypeDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeDecisions extends ListRecords
{
    protected static string $resource = TypeDecisionResource::class;

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
