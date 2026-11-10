<?php

namespace App\Modules\DecisionRecours\Filament\Resources\SectionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionResource::class;

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
