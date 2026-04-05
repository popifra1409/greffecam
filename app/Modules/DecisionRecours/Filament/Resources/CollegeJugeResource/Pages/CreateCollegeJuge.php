<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollegeJuge extends CreateRecord
{
    protected static string $resource = CollegeJugeResource::class;

    // ✅ Déclaration explicite
    public array $cachedMembres = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraire les membres avant la création
        $membres = $data['membres'] ?? [];
        unset($data['membres']);

        // Stocker temporairement pour afterCreate
        $this->cachedMembres = $membres;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Attacher les juges au collège avec leurs qualités
        if (!empty($this->cachedMembres)) {
            foreach ($this->cachedMembres as $membre) {
                if (!empty($membre['juge_id']) && !empty($membre['qualite'])) {
                    $this->record->juges()->attach($membre['juge_id'], [
                        'qualite' => $membre['qualite'],
                    ]);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Collège créé avec succès';
    }
}