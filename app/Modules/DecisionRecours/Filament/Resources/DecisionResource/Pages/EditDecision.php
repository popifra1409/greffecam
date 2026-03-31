<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDecision extends EditRecord
{
    protected static string $resource = DecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn($record) => $record->estModifiable()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // ✅ NOUVEAU WORKFLOW : Nettoyer les champs conditionnels

        // Nettoyer les champs selon le type de recours
        if (empty($data['type_recours'])) {
            // Si pas de recours, vider tous les champs de recours
            $data['lettre_appel_reference'] = null;
            $data['lettre_appel_date'] = null;
            $data['lettre_appel_fichier'] = null;
            $data['lettre_opposition_reference'] = null;
            $data['lettre_opposition_date'] = null;
            $data['lettre_opposition_fichier'] = null;
        } elseif ($data['type_recours'] === 'appel') {
            // Si appel, vider les champs opposition
            $data['lettre_opposition_reference'] = null;
            $data['lettre_opposition_date'] = null;
            $data['lettre_opposition_fichier'] = null;
        } elseif ($data['type_recours'] === 'opposition') {
            // Si opposition, vider les champs appel
            $data['lettre_appel_reference'] = null;
            $data['lettre_appel_date'] = null;
            $data['lettre_appel_fichier'] = null;

            // ⚠️ VALIDATION : Opposition requiert signification
            if (!($data['est_signifiee'] ?? false)) {
                \Filament\Notifications\Notification::make()
                    ->title('Erreur de validation')
                    ->body('Une opposition ne peut être enregistrée que si la décision a été signifiée.')
                    ->danger()
                    ->send();

                // Annuler la sauvegarde
                $this->halt();
            }
        }

        // Si pas signifiée, vider les champs de signification
        if (!($data['est_signifiee'] ?? false)) {
            $data['date_signification'] = null;
            $data['reference_acte_huissier'] = null;
            $data['fichier_signification'] = null;
        }

        // Nettoyer les champs selon le mode de composition
        if (($data['mode_composition'] ?? null) === 'juge_unique') {
            $data['college_juge_id'] = null;
        } elseif (($data['mode_composition'] ?? null) === 'college') {
            $data['juge_unique_id'] = null;
        }

        // Si archivée, empêcher les modifications
        if ($this->record->is_archived) {
            \Filament\Notifications\Notification::make()
                ->title('Modification impossible')
                ->body('Cette décision est archivée et ne peut plus être modifiée.')
                ->warning()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Décision mise à jour avec succès';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}