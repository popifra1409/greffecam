<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtatDecisionController;
use App\Models\Sequestre;
use App\Services\EtatSequestrePdfService;
use App\Models\MouvementSequestre;
use App\Services\RecuMouvementPdfService;


Route::get('/', function () {
    return redirect('/portal');
});

// Routes pour l'état de décision
Route::get('/decisions/{decision}/etat/apercu', [EtatDecisionController::class, 'apercu'])
    ->name('decisions.etat.apercu')
    ->middleware('auth');

Route::get('/decisions/{decision}/etat/telecharger', [EtatDecisionController::class, 'telecharger'])
    ->name('decisions.etat.telecharger')
    ->middleware('auth');

Route::get('/sequestres/{sequestre}/etat-pdf', function (Sequestre $sequestre, EtatSequestrePdfService $service) {
    $nomFichier = 'etat-sequestre-' . str_replace(['/', '\\'], '-', $sequestre->numero_dossier_sequestre) . '.pdf';

    return $service->genererEtat($sequestre)->stream($nomFichier);
})->name('sequestres.etat.pdf')->middleware(['web', 'auth']);

Route::get('/mouvements-sequestre/{mouvement}/recu-pdf', function (MouvementSequestre $mouvement, RecuMouvementPdfService $service) {
    $type = $mouvement->type_mouvement === 'versement' ? 'versement' : 'retrait';
    $nomFichier = "recu-{$type}-{$mouvement->id}.pdf";

    return $service->genererRecu($mouvement)->stream($nomFichier);
})->name('mouvements.recu.pdf')->middleware(['web', 'auth']);

Route::get('/sequestres/rapport-consolide/pdf', function (\Illuminate\Http\Request $request) {
    $dateDebut = $request->query('date_debut');
    $dateFin = $request->query('date_fin');
    $statutId = $request->query('statut_sequestre_id');
    $natureId = $request->query('nature_sequestre_id');

    $filtreDates = function ($query) use ($dateDebut, $dateFin) {
        if ($dateDebut) $query->whereDate('date_mouvement', '>=', $dateDebut);
        if ($dateFin) $query->whereDate('date_mouvement', '<=', $dateFin);
    };

    $sequestres = Sequestre::query()
        ->with(['natureSequestre', 'statutSequestre'])
        ->when($statutId, fn($q) => $q->where('statut_sequestre_id', $statutId))
        ->when($natureId, fn($q) => $q->where('nature_sequestre_id', $natureId))
        ->addSelect([
            'total_entrees_periode' => MouvementSequestre::selectRaw('coalesce(sum(montant_mouvement), 0)')
                ->whereColumn('sequestre_id', 'sequestres.id')
                ->where('type_mouvement', 'versement')
                ->tap($filtreDates),
            'total_sorties_periode' => MouvementSequestre::selectRaw('coalesce(sum(montant_mouvement), 0)')
                ->whereColumn('sequestre_id', 'sequestres.id')
                ->where('type_mouvement', 'retrait')
                ->tap($filtreDates),
            'total_precompte_periode' => MouvementSequestre::selectRaw('coalesce(sum(montant_precompte), 0)')
                ->whereColumn('sequestre_id', 'sequestres.id')
                ->tap($filtreDates),
            'solde_courant' => MouvementSequestre::selectRaw('solde_apres')
                ->whereColumn('sequestre_id', 'sequestres.id')
                ->orderByDesc('date_mouvement')
                ->orderByDesc('id')
                ->limit(1),
        ])
        ->get();

    $data = [
        'sequestres' => $sequestres,
        'dateDebut' => $dateDebut,
        'dateFin' => $dateFin,
        'totalEntrees' => $sequestres->sum('total_entrees_periode'),
        'totalSorties' => $sequestres->sum('total_sorties_periode'),
        'totalPrecompte' => $sequestres->sum('total_precompte_periode'),
        'totalSolde' => $sequestres->sum('solde_courant'),
        'dateImpression' => now(),
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sequestres.pages.rapport-consolide-pdf', $data);
    $pdf->setPaper('a4', 'landscape');

    return $pdf->stream('rapport-consolide-sequestres.pdf');
})->name('sequestres.rapport-consolide.pdf')->middleware(['web', 'auth']);
