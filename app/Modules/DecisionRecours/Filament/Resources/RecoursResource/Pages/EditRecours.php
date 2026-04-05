<?php

namespace App\Modules\DecisionRecours\Filament\Resources\RecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\RecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecours extends EditRecord
{
    protected static string $resource = RecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Recours mis à jour avec succès';
    }
}