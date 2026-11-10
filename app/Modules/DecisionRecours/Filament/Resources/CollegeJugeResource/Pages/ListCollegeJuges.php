<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollegeJuges extends ListRecords
{
    protected static string $resource = CollegeJugeResource::class;

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
