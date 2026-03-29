<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DecisionResource;
use App\Models\Dossier;
use Filament\Resources\Pages\CreateRecord;

class CreateDecision extends CreateRecord
{
    protected static string $resource = DecisionResource::class;

    // ✅ PRÉ-REMPLIR LE FORMULAIRE AU CHARGEMENT
    public function mount(): void
    {
        parent::mount();

        // Récupérer dossier_id depuis l'URL
        $dossierId = request()->query('dossier_id');

        if ($dossierId) {
            $dossier = Dossier::with(['tribunal', 'section', 'matiere', 'anneeJudiciaire'])
                ->find($dossierId);

            if ($dossier) {
                // Pré-remplir le formulaire
                $this->form->fill([
                    'dossier_id' => $dossier->id,
                    'tribunal_id' => $dossier->tribunal_id,
                    'section_id' => $dossier->section_id,
                    'matiere_id' => $dossier->matiere_id,
                    'annee_judiciaire_id' => $dossier->annee_judiciaire_id,
                ]);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si pas de dossier_id, pas de mutation
        if (empty($data['dossier_id'])) {
            return $data;
        }

        // Récupérer le dossier pour hériter ses informations
        $dossier = Dossier::with(['tribunal', 'section', 'matiere', 'anneeJudiciaire'])
            ->find($data['dossier_id']);

        if ($dossier) {
            // S'assurer que les IDs sont bien présents
            $data['tribunal_id'] = $dossier->tribunal_id;
            $data['section_id'] = $dossier->section_id;
            $data['matiere_id'] = $dossier->matiere_id;
            $data['annee_judiciaire_id'] = $dossier->annee_judiciaire_id;

            // Définir le greffier responsable par défaut (l'utilisateur actuel)
            $data['greffier_responsable_id'] = $data['greffier_responsable_id'] ?? auth()->id();
            $data['detenteur_actuel_id'] = auth()->id();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Rediriger vers la vue de la décision après création
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
