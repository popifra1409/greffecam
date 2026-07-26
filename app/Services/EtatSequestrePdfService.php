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
            'documents',
        ]);

        $data = [
            'sequestre' => $sequestre,
            'dateImpression' => now(),
        ];

        $pdf = Pdf::loadView('sequestres.etat-sequestre', $data);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}
