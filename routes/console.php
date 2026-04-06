<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ========================================
// PLANIFICATION NOTIFICATIONS RECOURS
// ========================================

// Résumé quotidien à 8h (Lundi-Vendredi)
Schedule::command('notifications:envoyer-recours --type=resume')
    ->dailyAt('08:00')
    ->weekdays() // Seulement les jours ouvrables
    ->timezone('Africa/Douala')
    ->emailOutputOnFailure('admin@greffe.cm'); // Notifier en cas d'erreur

// Vérification urgences à 12h (Tous les jours)
Schedule::command('notifications:envoyer-recours --type=urgent')
    ->dailyAt('12:00')
    ->timezone('Africa/Douala');

// Vérification urgences à 16h (Tous les jours)
Schedule::command('notifications:envoyer-recours --type=urgent')
    ->dailyAt('16:00')
    ->timezone('Africa/Douala');

// ========================================
// TÂCHES DE MAINTENANCE
// ========================================

// Nettoyer les vieilles notifications (tous les dimanches à 2h)
Schedule::command('notifications:prune')
    ->weeklyOn(0, '02:00')
    ->timezone('Africa/Douala');

// Nettoyer les logs (tous les mois)
Schedule::command('log:clear')
    ->monthly()
    ->timezone('Africa/Douala');

// ========================================
// MODE DEBUG UNIQUEMENT
// ========================================

if (config('app.debug')) {
    // Test toutes les 5 minutes en mode debug
    Schedule::command('notifications:envoyer-recours --type=resume')
        ->everyFiveMinutes()
        ->appendOutputTo(storage_path('logs/scheduler-debug.log'));
}