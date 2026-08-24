<?php

namespace App\Services;

use App\Models\SequestrePartieAdverse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EcheancierService
{
    /**
     * Nombre de mois correspondant à chaque périodicité.
     */
    protected function moisParPeriode(string $periodicite): int
    {
        return match ($periodicite) {
            'mensuel' => 1,
            'trimestriel' => 3,
            'semestriel' => 6,
            'annuel' => 12,
            default => 1,
        };
    }

    /**
     * Génère l'échéancier complet d'une partie adverse : une ligne par échéance,
     * avec son statut calculé par comparaison de cumuls (méthode "aging").
     *
     * Retourne une Collection de tableaux :
     * ['numero', 'date_echeance', 'montant_attendu_cumule', 'montant_verse_cumule',
     *  'reste_periode', 'statut', 'statut_label', 'statut_couleur']
     */
    public function genererEcheancier(SequestrePartieAdverse $partieAdverse): Collection
    {
        if (empty($partieAdverse->date_debut_paiement) || empty($partieAdverse->montant_echeance)) {
            return collect();
        }

        $moisParPeriode = $this->moisParPeriode($partieAdverse->periodicite);
        $dateDebut = Carbon::parse($partieAdverse->date_debut_paiement);

        // Nombre total d'échéances à générer
        if ($partieAdverse->duree_contrat_mois) {
            $nombreEcheances = (int) ceil($partieAdverse->duree_contrat_mois / $moisParPeriode);
        } else {
            // Échéancier glissant : jusqu'à aujourd'hui + 1 échéance à venir
            $moisEcoules = $dateDebut->diffInMonths(now()) + $moisParPeriode;
            $nombreEcheances = max(1, (int) ceil($moisEcoules / $moisParPeriode));
        }

        // Récupérer tous les versements de cette partie adverse, triés par date
        $versements = $partieAdverse->mouvements()
            ->where('type_mouvement', 'versement')
            ->orderBy('date_mouvement')
            ->get(['date_mouvement', 'montant_mouvement']);

        $echeancier = collect();
        $cumulAttenduPrecedent = 0;

        for ($n = 1; $n <= $nombreEcheances; $n++) {
            $dateEcheance = $dateDebut->copy()->addMonths($moisParPeriode * ($n - 1));
            $cumulAttendu = $n * $partieAdverse->montant_echeance;

            // Cumul versé jusqu'à cette date d'échéance (inclus)
            $cumulVerse = $versements
                ->filter(fn($v) => Carbon::parse($v->date_mouvement)->lessThanOrEqualTo($dateEcheance))
                ->sum('montant_mouvement');

            [$statut, $label, $couleur] = $this->determinerStatut(
                $cumulVerse,
                $cumulAttendu,
                $cumulAttenduPrecedent,
                $dateEcheance
            );

            $echeancier->push([
                'numero' => $n,
                'date_echeance' => $dateEcheance,
                'montant_echeance' => $partieAdverse->montant_echeance,
                'montant_attendu_cumule' => $cumulAttendu,
                'montant_verse_cumule' => $cumulVerse,
                'reste_periode' => max(0, $cumulAttendu - $cumulVerse),
                'statut' => $statut,
                'statut_label' => $label,
                'statut_couleur' => $couleur,
            ]);

            $cumulAttenduPrecedent = $cumulAttendu;
        }

        return $echeancier;
    }

    protected function determinerStatut(float $cumulVerse, float $cumulAttendu, float $cumulAttenduPrecedent, Carbon $dateEcheance): array
    {
        if ($cumulVerse >= $cumulAttendu) {
            return ['payee', 'Payée', 'success'];
        }

        if ($cumulVerse > $cumulAttenduPrecedent) {
            return ['partielle', 'Partielle', 'warning'];
        }

        if ($dateEcheance->isPast()) {
            return ['en_retard', 'En retard', 'danger'];
        }

        return ['a_venir', 'À venir', 'gray'];
    }

    /**
     * Résumé rapide de la situation actuelle (pour affichage compact).
     */
    public function situationActuelle(SequestrePartieAdverse $partieAdverse): array
    {
        $echeancier = $this->genererEcheancier($partieAdverse);

        if ($echeancier->isEmpty()) {
            return [
                'statut_global' => 'non_configure',
                'statut_label' => 'Échéancier non configuré',
                'statut_couleur' => 'gray',
                'reste_a_payer' => 0,
                'prochaine_echeance' => null,
                'derniere_echeance_en_retard' => null,
            ];
        }

        $echeancesDues = $echeancier->filter(fn($e) => $e['date_echeance']->lessThanOrEqualTo(now()));
        $derniereEcheanceDue = $echeancesDues->last();

        $prochaineEcheance = $echeancier->first(fn($e) => $e['date_echeance']->isFuture());
        $premiereEnRetard = $echeancier->first(fn($e) => $e['statut'] === 'en_retard');

        return [
            'statut_global' => $derniereEcheanceDue['statut'] ?? 'a_venir',
            'statut_label' => $derniereEcheanceDue['statut_label'] ?? 'À venir',
            'statut_couleur' => $derniereEcheanceDue['statut_couleur'] ?? 'gray',
            'reste_a_payer' => $derniereEcheanceDue['reste_periode'] ?? 0,
            'prochaine_echeance' => $prochaineEcheance['date_echeance'] ?? null,
            'derniere_echeance_en_retard' => $premiereEnRetard['date_echeance'] ?? null,
        ];
    }
}
