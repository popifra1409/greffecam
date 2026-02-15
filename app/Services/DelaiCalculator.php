<?php

namespace App\Services;

use App\Models\JourFerie;
use Carbon\Carbon;

class DelaiCalculator
{
    /**
     * Calculer la date limite en excluant les week-ends et jours fériés
     */
    public static function calculerDateLimite(Carbon $dateDepart, int $nombreJours): Carbon
    {
        $dateLimite = $dateDepart->copy();
        $joursAjoutes = 0;

        // Récupérer les jours fériés de l'année
        $annee = $dateDepart->year;
        $joursFeries = JourFerie::whereYear('date', $annee)
            ->orWhereYear('date', $annee + 1)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        while ($joursAjoutes < $nombreJours) {
            $dateLimite->addDay();

            // Vérifier si c'est un week-end
            if ($dateLimite->isWeekend()) {
                continue;
            }

            // Vérifier si c'est un jour férié
            if (in_array($dateLimite->format('Y-m-d'), $joursFeries)) {
                continue;
            }

            $joursAjoutes++;
        }

        return $dateLimite;
    }

    /**
     * Calculer le nombre de jours restants (jours ouvrables)
     */
    public static function calculerJoursRestants(Carbon $dateLimite): int
    {
        $aujourd_hui = Carbon::now()->startOfDay();
        $dateLimite = $dateLimite->copy()->startOfDay();

        if ($dateLimite->isPast()) {
            return 0;
        }

        $joursRestants = 0;
        $dateActuelle = $aujourd_hui->copy();

        // Récupérer les jours fériés
        $annee = $aujourd_hui->year;
        $joursFeries = JourFerie::whereYear('date', $annee)
            ->orWhereYear('date', $annee + 1)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        while ($dateActuelle->lessThan($dateLimite)) {
            if (!$dateActuelle->isWeekend() && !in_array($dateActuelle->format('Y-m-d'), $joursFeries)) {
                $joursRestants++;
            }
            $dateActuelle->addDay();
        }

        return $joursRestants;
    }

    /**
     * Déterminer le niveau d'alerte selon les jours restants
     */
    public static function determinerNiveauAlerte(int $joursRestants): ?string
    {
        if ($joursRestants <= 2) {
            return 'rouge'; // H-48
        } elseif ($joursRestants <= 7) {
            return 'orange'; // J-7
        } elseif ($joursRestants <= 15) {
            return 'jaune'; // J-15
        }

        return null; // Pas d'alerte
    }
}
