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
