<?php

namespace App\Modules\DecisionRecours\Filament\Resources\RecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\RecoursResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecours extends CreateRecord
{
    protected static string $resource = RecoursResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Recours créé avec succès';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si le numéro n'est pas défini, le générer
        if (empty($data['numero_recours'])) {
            $year = now()->year;
            $count = \App\Models\Recours::whereYear('created_at', $year)->count() + 1;
            $data['numero_recours'] = 'REC/' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}