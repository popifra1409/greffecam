<?php

namespace App\Services;

use App\Models\Decision;
use Barryvdh\DomPDF\Facade\Pdf;

class EtatDecisionPdfService
{
    public function genererEtat(Decision $decision, string $type = 'complet'): \Barryvdh\DomPDF\PDF
    {
        $decision->load([
            'dossier.tribunal',
            'dossier.section',
            'dossier.matiere',
            'dossier.anneeJudiciaire',
            'dossier.demandeurs',
            'dossier.defendeurs',
            'dossier.infractions',
            'natureDecision',
            'jugeUnique',
            'collegeJuge.juges',
            'greffierDecision',
            'greffierResponsable',
        ]);

        $data = [
            'decision' => $decision,
            'type' => $type,
            'dateImpression' => now(),
        ];

        $pdf = Pdf::loadView('pdf.etat-decision', $data);

        // Configuration PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('margin-top', '15mm');
        $pdf->setOption('margin-right', '15mm');
        $pdf->setOption('margin-bottom', '15mm');
        $pdf->setOption('margin-left', '15mm');

        return $pdf;
    }

    public function apercu(Decision $decision): \Barryvdh\DomPDF\PDF
    {
        return $this->genererEtat($decision, 'complet');
    }

    public function telecharger(Decision $decision): \Barryvdh\DomPDF\PDF
    {
        return $this->genererEtat($decision, 'complet');
    }
}
