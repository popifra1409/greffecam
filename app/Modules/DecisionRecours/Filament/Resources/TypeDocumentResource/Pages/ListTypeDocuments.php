<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TypeDocumentResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TypeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeDocuments extends ListRecords
{
    protected static string $resource = TypeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
