<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollegeJuge extends EditRecord
{
    protected static string $resource = CollegeJugeResource::class;

    // ✅ AJOUT : Déclaration explicite de la propriété
    public array $cachedMembres = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Charger les membres existants
        $membres = $this->record->juges->map(function ($juge) {
            return [
                'juge_id' => $juge->id,
                'qualite' => $juge->pivot->qualite,
            ];
        })->toArray();

        $data['membres'] = $membres;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraire les membres avant la sauvegarde
        $membres = $data['membres'] ?? [];
        unset($data['membres']);

        // Stocker temporairement pour afterSave
        $this->cachedMembres = $membres;

        return $data;
    }

    protected function afterSave(): void
    {
        // Détacher tous les juges existants
        $this->record->juges()->detach();

        // Réattacher les juges avec leurs nouvelles qualités
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
}