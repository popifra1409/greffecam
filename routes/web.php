<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtatDecisionController;


Route::get('/', function () {
    return view('welcome');
});

// Routes pour l'état de décision
Route::get('/decisions/{decision}/etat/apercu', [EtatDecisionController::class, 'apercu'])
    ->name('decisions.etat.apercu')
    ->middleware('auth');

Route::get('/decisions/{decision}/etat/telecharger', [EtatDecisionController::class, 'telecharger'])
    ->name('decisions.etat.telecharger')
    ->middleware('auth');
