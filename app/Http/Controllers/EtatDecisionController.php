<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use App\Services\EtatDecisionPdfService;
use Illuminate\Http\Request;

class EtatDecisionController extends Controller
{
    protected $pdfService;

    public function __construct(EtatDecisionPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function apercu(Decision $decision)
    {
        $pdf = $this->pdfService->apercu($decision);
        return $pdf->stream("etat-decision-{$decision->numero_repertoire}.pdf");
    }

    public function telecharger(Decision $decision)
    {
        $pdf = $this->pdfService->telecharger($decision);
        return $pdf->download("etat-decision-{$decision->numero_repertoire}.pdf");
    }
}
