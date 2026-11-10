<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDecisions extends ListRecords
{
    protected static string $resource = DecisionResource::class;

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
