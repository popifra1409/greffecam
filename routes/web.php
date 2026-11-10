<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditArchiveDownloadController;
use App\Http\Controllers\EtatDecisionController;
use App\Models\Sequestre;
use App\Services\EtatSequestrePdfService;
use App\Models\MouvementSequestre;
use App\Services\RecuMouvementPdfService;
use App\Services\BanqueEtatService;
use App\Services\MouvementsEtatPdfService;
use App\Services\RapportConsolideService;


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

Route::get('/audit/archives/{archive}/telecharger', AuditArchiveDownloadController::class)
    ->name('audit.archives.telecharger')
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

Route::get('/sequestres/{sequestre}/mouvements/etat-pdf', function (\Illuminate\Http\Request $request, Sequestre $sequestre, MouvementsEtatPdfService $service) {
    $filtres = [
        'date_debut' => $request->query('date_debut'),
        'date_fin' => $request->query('date_fin'),
        'type_mouvement' => $request->query('type_mouvement'),
        'partie_adverse_id' => $request->query('partie_adverse_id'),
        'ayant_droit_id' => $request->query('ayant_droit_id'),
        'versements_locataires' => (bool) $request->query('versements_locataires'),
        'retraits_ayants_droit' => (bool) $request->query('retraits_ayants_droit'),
        'autres_retraits' => (bool) $request->query('autres_retraits'),
    ];

    $nomFichier = 'etat-mouvements-' . str_replace(['/', '\\'], '-', $sequestre->numero_dossier_sequestre) . '.pdf';

    return $service->generer($sequestre, $filtres)->stream($nomFichier);
})->name('mouvements.etat.pdf')->middleware(['web', 'auth']);

Route::get('/sequestres/rapport-consolide/pdf', function (\Illuminate\Http\Request $request, RapportConsolideService $service) {
    $dateDebut = $request->query('date_debut');
    $dateFin = $request->query('date_fin');

    $sequestres = $service->requete(
        dateDebut: $dateDebut,
        dateFin: $dateFin,
        statutId: $request->query('statut_sequestre_id'),
        natureId: $request->query('nature_sequestre_id'),
    )->get();

    $data = [
        'sequestres' => $sequestres,
        'dateDebut' => $dateDebut,
        'dateFin' => $dateFin,
        'libelleSolde' => $service->libelleSolde($dateFin),
        'libelleOuverture' => $service->libelleOuverture($dateDebut),
        'dateImpression' => now(),
        ...$service->totaux($sequestres),
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sequestres.pages.rapport-consolide-pdf', $data);
    $pdf->setPaper('a4', 'landscape');

    \App\Support\JournalAudit::journaliser('rapports', 'pdf_genere', [
        'rapport' => 'Rapport consolidé des séquestres',
        'date_debut' => $dateDebut,
        'date_fin' => $dateFin,
        'statut_sequestre_id' => $request->query('statut_sequestre_id'),
        'nature_sequestre_id' => $request->query('nature_sequestre_id'),
        'sequestres' => $sequestres->count(),
    ]);

    return $pdf->stream('rapport-consolide-sequestres.pdf');
})->name('sequestres.rapport-consolide.pdf')->middleware(['web', 'auth']);

Route::get('/sequestres/banque/etat-pdf', function (\Illuminate\Http\Request $request, BanqueEtatService $service) {
    $dateArrete = \Carbon\Carbon::parse($request->query('date_arret') ?: now()->toDateString())->toDateString();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sequestres.pages.banque-etat-pdf', [
        'lignes' => $service->lignes($dateArrete),
        'dateArrete' => $dateArrete,
        'tauxInteret' => config('banque.taux_interet_annuel'),
        'tauxTva' => config('banque.taux_tva_interet'),
        'dateImpression' => now(),
    ]);
    $pdf->setPaper('a4', 'landscape');

    \App\Support\JournalAudit::journaliser('rapports', 'pdf_genere', [
        'rapport' => 'État des comptes bancaires',
        'date_arret' => $dateArrete,
    ]);

    return $pdf->stream('etat-comptes-bancaires.pdf');
})->name('banque.etat.pdf')->middleware(['web', 'auth']);
