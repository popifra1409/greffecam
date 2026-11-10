<?php

namespace App\Services;

use App\Models\MouvementSequestre;
use App\Models\Sequestre;
use App\Support\JournalAudit;
use Barryvdh\DomPDF\Facade\Pdf;

class EtatSequestrePdfService
{
    public function genererEtat(Sequestre $sequestre): \Barryvdh\DomPDF\PDF
    {
        // ✅ Les tableaux volumineux (historique des mouvements) sont coûteux en
        // mémoire pour DomPDF ; on augmente temporairement la limite pour ce rendu.
        ini_set('memory_limit', '512M');

            $sequestre->load([
            'dossier.tribunal',
            'decision.typeDecision',
            'decision.natureDecision',
            'representant',
            'natureSequestre',
            'statutSequestre',
            'ayantsDroit',
            'partiesAdverses',
            'mouvements.motifMouvement',
            'mouvements.ayantDroit',
            'mouvements.partieAdverse',
            'mouvements.partieTierce',
            'documents',
        ]);

        $data = [
            'sequestre' => $sequestre,
            'repartitionAyantsDroit' => $this->calculerRepartitionAyantsDroit($sequestre),
            'dateImpression' => now(),
        ];

        $pdf = Pdf::loadView('sequestres.etat-sequestre', $data);

        $pdf->setPaper('a4', 'portrait');

        JournalAudit::journaliser('rapports', 'pdf_genere', [
            'rapport' => 'État du séquestre',
            'numero' => $sequestre->numero_dossier_sequestre,
        ], $sequestre);

        return $pdf;
    }

    /**
     * Calcule, pour chaque ayant droit enregistré sur ce séquestre, le nombre
     * de retraits et le montant total déjà perçu — c'est le cœur du "rapport
     * par dossier famille" demandé : savoir ce que chacun a déjà touché.
     *
     * Les lignes sont ensuite regroupées par type de bénéficiaire (ayant droit
     * et son rôle de succession, partie tierce, partie adverse, mandataire,
     * autre bénéficiaire, non attribué) pour produire des sous-totaux.
     */
    protected function calculerRepartitionAyantsDroit(Sequestre $sequestre): \Illuminate\Support\Collection
    {
        $retraits = $sequestre->mouvements->where('type_mouvement', 'retrait');

        $lignes = $sequestre->ayantsDroit->map(function ($ayantDroit) use ($retraits) {
            $retraitsAyantDroit = $retraits->where('sequestre_ayant_droit_id', $ayantDroit->id);

            return [
                'type' => $ayantDroit->role_succession
                    ? 'Ayant droit (' . $ayantDroit->role_succession_label . ')'
                    : 'Ayant droit',
                'ayant_droit' => $ayantDroit,
                'libelle' => $ayantDroit->nom_complet,
                'nombre_retraits' => $retraitsAyantDroit->count(),
                'total_percu' => $retraitsAyantDroit->sum('montant_mouvement'),
                'dont_procuration' => $retraitsAyantDroit->where('est_procuration', true)->count(),
            ];
        });

        // Retraits historiques non rattachés à un ayant droit : on les regroupe
        // par bénéficiaire réel au lieu de tout fondre sous « Non attribué ».
        $sansAyantDroit = $retraits->whereNull('sequestre_ayant_droit_id');

        $groupes = $sansAyantDroit->groupBy(function (MouvementSequestre $mouvement) {
            if ($mouvement->sequestre_partie_tierce_id) {
                return 'tierce:' . $mouvement->sequestre_partie_tierce_id;
            }

            if ($mouvement->sequestre_partie_adverse_id) {
                return 'adverse:' . $mouvement->sequestre_partie_adverse_id;
            }

            if ($mouvement->est_procuration && filled($mouvement->mandataire_nom)) {
                return 'mandataire:' . mb_strtolower(trim($mouvement->mandataire_nom));
            }

            if (filled($mouvement->operateur_beneficiaire)) {
                return 'operateur:' . mb_strtolower(trim($mouvement->operateur_beneficiaire));
            }

            return 'inconnu';
        });

        foreach ($groupes as $groupe) {
            $lignes->push([
                'type' => $this->typeBeneficiaireHistorique($groupe->first()),
                'ayant_droit' => null,
                'libelle' => $this->libelleBeneficiaireHistorique($groupe->first()),
                'nombre_retraits' => $groupe->count(),
                'total_percu' => $groupe->sum('montant_mouvement'),
                'dont_procuration' => $groupe->where('est_procuration', true)->count(),
            ]);
        }

        return $lignes
            ->groupBy('type')
            ->sortBy(fn(\Illuminate\Support\Collection $groupe, string $type): string => $this->rangType($type) . '|' . $type)
            ->map(fn(\Illuminate\Support\Collection $groupe, string $type) => [
                'type' => $type,
                'lignes' => $groupe->values(),
                'nombre_retraits' => $groupe->sum('nombre_retraits'),
                'dont_procuration' => $groupe->sum('dont_procuration'),
                'total_percu' => $groupe->sum('total_percu'),
            ])
            ->values();
    }

    /**
     * Ordre d'affichage stable : ayants droit, parties tierces, parties
     * adverses, mandataires, autres bénéficiaires, puis non attribués.
     */
    protected function rangType(string $type): int
    {
        return match (true) {
            str_starts_with($type, 'Ayant droit') => 1,
            str_starts_with($type, 'Partie tierce') => 2,
            $type === 'Partie adverse' => 3,
            $type === 'Mandataire' => 4,
            $type === 'Autre bénéficiaire' => 5,
            default => 9,
        };
    }

    protected function typeBeneficiaireHistorique(MouvementSequestre $mouvement): string
    {
        if ($mouvement->sequestre_partie_tierce_id) {
            $type = $mouvement->partieTierce?->type_label;

            return $type ? 'Partie tierce : ' . $type : 'Partie tierce';
        }

        if ($mouvement->sequestre_partie_adverse_id) {
            return 'Partie adverse';
        }

        if ($mouvement->est_procuration && filled($mouvement->mandataire_nom)) {
            return 'Mandataire';
        }

        if (filled($mouvement->operateur_beneficiaire)) {
            return 'Autre bénéficiaire';
        }

        return 'Non attribué';
    }

    protected function libelleBeneficiaireHistorique(MouvementSequestre $mouvement): string
    {
        if ($mouvement->sequestre_partie_tierce_id) {
            $tierce = $mouvement->partieTierce;

            return $tierce
                ? $tierce->nom_complet . ' (' . $tierce->type_label . ')'
                : 'Partie tierce #' . $mouvement->sequestre_partie_tierce_id;
        }

        if ($mouvement->sequestre_partie_adverse_id) {
            $adverse = $mouvement->partieAdverse;

            return $adverse?->nom_complet ?? 'Partie adverse #' . $mouvement->sequestre_partie_adverse_id;
        }

        if ($mouvement->est_procuration && filled($mouvement->mandataire_nom)) {
            return $mouvement->mandataire_nom . ' (mandataire)';
        }

        if (filled($mouvement->operateur_beneficiaire)) {
            return $mouvement->operateur_beneficiaire;
        }

        return 'Non attribué';
    }
}
