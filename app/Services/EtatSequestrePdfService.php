<?php

namespace App\Services;

use App\Models\Sequestre;
use Barryvdh\DomPDF\Facade\Pdf;

class EtatSequestrePdfService
{
    public function genererEtat(Sequestre $sequestre): \Barryvdh\DomPDF\PDF
    {
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
            'documents',
        ]);

        $data = [
            'sequestre' => $sequestre,
            'repartitionAyantsDroit' => $this->calculerRepartitionAyantsDroit($sequestre),
            'dateImpression' => now(),
        ];

        $pdf = Pdf::loadView('sequestres.etat-sequestre', $data);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Calcule, pour chaque ayant droit enregistré sur ce séquestre, le nombre
     * de retraits et le montant total déjà perçu — c'est le cœur du "rapport
     * par dossier famille" demandé : savoir ce que chacun a déjà touché.
     */
    protected function calculerRepartitionAyantsDroit(Sequestre $sequestre): \Illuminate\Support\Collection
    {
        $retraits = $sequestre->mouvements->where('type_mouvement', 'retrait');

        $repartition = $sequestre->ayantsDroit->map(function ($ayantDroit) use ($retraits) {
            $retraitsAyantDroit = $retraits->where('sequestre_ayant_droit_id', $ayantDroit->id);

            return [
                'ayant_droit' => $ayantDroit,
                'nombre_retraits' => $retraitsAyantDroit->count(),
                'total_percu' => $retraitsAyantDroit->sum('montant_mouvement'),
                'dont_procuration' => $retraitsAyantDroit->where('est_procuration', true)->count(),
            ];
        });

        // Retraits historiques non rattachés à un ayant droit précis (avant l'ajout de ce champ)
        $sansAyantDroit = $retraits->whereNull('sequestre_ayant_droit_id');

        if ($sansAyantDroit->count() > 0) {
            $repartition->push([
                'ayant_droit' => null,
                'nombre_retraits' => $sansAyantDroit->count(),
                'total_percu' => $sansAyantDroit->sum('montant_mouvement'),
                'dont_procuration' => 0,
            ]);
        }

        return $repartition;
    }
}
