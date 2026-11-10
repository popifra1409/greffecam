<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TypeRecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TypeRecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeRecours extends ListRecords
{
    protected static string $resource = TypeRecoursResource::class;

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
