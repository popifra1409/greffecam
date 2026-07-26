<?php

namespace App\Services;

use App\Models\MouvementSequestre;
use App\Helpers\NombreEnLettresHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class RecuMouvementPdfService
{
    public function genererRecu(MouvementSequestre $mouvement): \Barryvdh\DomPDF\PDF
    {
        $mouvement->load([
            'sequestre.dossier.tribunal',
            'motifMouvement',
            'partieAdverse',
            'ayantDroit',
        ]);

        $data = [
            'mouvement' => $mouvement,
            'numeroRecu' => $this->genererNumeroRecu($mouvement),
            'montantEnLettres' => NombreEnLettresHelper::convertir((int) round($mouvement->montant_mouvement)),
            'dateImpression' => now(),
        ];

        $vue = $mouvement->type_mouvement === 'versement'
            ? 'sequestres.recu-versement'
            : 'sequestres.recu-retrait';

        $pdf = Pdf::loadView($vue, $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    protected function genererNumeroRecu(MouvementSequestre $mouvement): string
    {
        $prefixe = $mouvement->type_mouvement === 'versement' ? 'REC-V' : 'REC-R';
        $numeroSequestre = str_replace('/', '-', $mouvement->sequestre->numero_dossier_sequestre);

        return sprintf('%s/%s/%04d', $prefixe, $numeroSequestre, $mouvement->id);
    }
}