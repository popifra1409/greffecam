<?php

namespace App\Modules\DecisionRecours\Filament\Resources\GreffierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\GreffierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGreffiers extends ListRecords
{
    protected static string $resource = GreffierResource::class;

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
