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
                    'date_decision' => now(),
                    'statut' => 'brouillon',
                    'mode_composition' => 'juge_unique',
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
        }

        // ✅ NOUVEAU WORKFLOW : Valeurs par défaut pour les nouveaux champs

        // Statut par défaut
        $data['statut'] = $data['statut'] ?? 'brouillon';

        // Mode de composition par défaut
        $data['mode_composition'] = $data['mode_composition'] ?? 'juge_unique';

        // Signification : par défaut non signifiée
        $data['est_signifiee'] = $data['est_signifiee'] ?? false;

        // Archivage : par défaut non archivée
        $data['is_archived'] = $data['is_archived'] ?? false;

        // ✅ Nettoyer les champs conditionnels selon le type de recours
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
        }

        // ✅ Si pas signifiée, vider les champs de signification
        if (!($data['est_signifiee'] ?? false)) {
            $data['date_signification'] = null;
            $data['reference_acte_huissier'] = null;
            $data['fichier_signification'] = null;
        }

        // ✅ Nettoyer les champs selon le mode de composition
        if (($data['mode_composition'] ?? null) === 'juge_unique') {
            $data['college_juge_id'] = null;
        } elseif (($data['mode_composition'] ?? null) === 'college') {
            $data['juge_unique_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // ✅ OPTIONNEL : Mettre à jour le statut du dossier si nécessaire
        $decision = $this->record;

        if ($decision->dossier) {
            // Si le dossier était "ouvert", passer en "en_instance"
            if ($decision->dossier->statut === 'ouvert') {
                $decision->dossier->update(['statut' => 'en_instance']);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        // Rediriger vers la vue de la décision après création
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    // ✅ Message de succès personnalisé
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Décision créée avec succès';
    }
}