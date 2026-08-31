<?php

namespace App\Services;

use App\Models\Sequestre;
use Illuminate\Support\Collection;

class RepartitionSuccessionService
{
    /**
     * Calcule la répartition du solde disponible entre les ayants droit,
     * selon la règle définie sur le séquestre.
     *
     * Retourne :
     * [
     *   'solde_disponible' => float,
     *   'repartitions' => Collection [
     *       ['ayant_droit' => SequestreAyantDroit, 'role_label' => string,
     *        'part_pourcentage' => float, 'montant' => float],
     *       ...
     *   ],
     *   'avertissement' => ?string,
     * ]
     */
    public function calculerRepartition(Sequestre $sequestre): array
    {
        $solde = (float) $sequestre->solde_actuel;
        $ayantsDroit = $sequestre->ayantsDroit;

        // ✅ Solde négatif ou nul : rien à répartir, on ne calcule aucune part
        if ($solde <= 0) {
            return [
                'solde_disponible' => $solde,
                'repartitions' => collect(),
                'avertissement' => $solde < 0
                    ? 'Le solde du séquestre est négatif (' . number_format($solde, 0, ',', ' ') . ' FCFA). Aucune répartition n\'est possible tant que le solde n\'est pas redevenu positif.'
                    : 'Le solde disponible est nul. Aucun montant à répartir pour le moment.',
            ];
        }

        if ($ayantsDroit->isEmpty()) {
            return [
                'solde_disponible' => $solde,
                'repartitions' => collect(),
                'avertissement' => 'Aucun ayant droit enregistré sur ce séquestre.',
            ];
        }

        return match ($sequestre->regle_repartition) {
            'succession_conjoint_enfants' => $this->repartirSuccessionAvecConjoint($solde, $ayantsDroit),
            'succession_enfants_seuls' => $this->repartirEnfantsSeuls($solde, $ayantsDroit),
            'separation_conjoints' => $this->repartirSeparation($solde, $ayantsDroit),
            'personnalisee' => $this->repartirPersonnalisee($solde, $ayantsDroit),
            default => [
                'solde_disponible' => $solde,
                'repartitions' => collect(),
                'avertissement' => 'Aucune règle de répartition définie pour ce séquestre (à choisir dans l\'onglet Caractéristiques).',
            ],
        };
    }

    /**
     * Décès d'un parent : conjoint(s) vivant(s) → 1/4 (partagé entre eux si
     * plusieurs coépouses), enfants → 3/4 (partagé à parts égales).
     * Filet de sécurité : s'il n'y a aucun conjoint enregistré, les 3/4
     * deviennent 100% pour les enfants (rien ne doit rester non attribué).
     */
    protected function repartirSuccessionAvecConjoint(float $solde, Collection $ayantsDroit): array
    {
        $conjoints = $ayantsDroit->where('role_succession', 'conjoint');
        $enfants = $ayantsDroit->where('role_succession', 'enfant');
        $autres = $ayantsDroit->whereNotIn('role_succession', ['conjoint', 'enfant']);

        $repartitions = collect();
        $avertissement = null;

        if ($conjoints->isEmpty() && $enfants->isEmpty()) {
            return [
                'solde_disponible' => $solde,
                'repartitions' => collect(),
                'avertissement' => 'Aucun ayant droit n\'a de rôle "Conjoint" ou "Enfant" défini. Renseignez le rôle successoral de chacun.',
            ];
        }

        if ($conjoints->isEmpty()) {
            // Pas de conjoint : les enfants récupèrent 100%
            $montantParEnfant = $enfants->isNotEmpty() ? $solde / $enfants->count() : 0;

            foreach ($enfants as $enfant) {
                $repartitions->push($this->ligne($enfant, 'Enfant', 100 / $enfants->count(), $montantParEnfant));
            }

            $avertissement = 'Aucun conjoint enregistré : les 3/4 initialement prévus pour le(s) conjoint(s) ont été réattribués aux enfants (répartition à 100%).';
        } else {
            $montantConjoints = $solde * 0.25;
            $montantEnfants = $solde * 0.75;

            $montantParConjoint = $montantConjoints / $conjoints->count();
            foreach ($conjoints as $conjoint) {
                $repartitions->push($this->ligne($conjoint, 'Conjoint', 25 / $conjoints->count(), $montantParConjoint));
            }

            if ($enfants->isNotEmpty()) {
                $montantParEnfant = $montantEnfants / $enfants->count();
                foreach ($enfants as $enfant) {
                    $repartitions->push($this->ligne($enfant, 'Enfant', 75 / $enfants->count(), $montantParEnfant));
                }
            } else {
                $avertissement = 'Aucun enfant enregistré : les 3/4 prévus pour les enfants ne sont pas attribués. Vérifiez la composition de la famille.';
            }
        }

        if ($autres->isNotEmpty()) {
            $avertissement = ($avertissement ? $avertissement . ' ' : '')
                . $autres->count() . ' ayant(s) droit sans rôle "Conjoint"/"Enfant" défini n\'ont reçu aucune part.';
        }

        return ['solde_disponible' => $solde, 'repartitions' => $repartitions, 'avertissement' => $avertissement];
    }

    /**
     * Pas de conjoint (parents déjà décédés, ou séquestre concernant
     * uniquement des enfants) : parts égales entre tous les enfants.
     */
    protected function repartirEnfantsSeuls(float $solde, Collection $ayantsDroit): array
    {
        $enfants = $ayantsDroit->where('role_succession', 'enfant');

        // Filet de sécurité : si personne n'a le rôle "enfant" mais qu'il y a
        // des ayants droit, on considère que tous sont des enfants par défaut.
        if ($enfants->isEmpty()) {
            $enfants = $ayantsDroit;
        }

        $montantParEnfant = $solde / $enfants->count();
        $repartitions = $enfants->map(fn($e) => $this->ligne($e, 'Enfant', 100 / $enfants->count(), $montantParEnfant));

        return ['solde_disponible' => $solde, 'repartitions' => $repartitions, 'avertissement' => null];
    }

    /**
     * Séparation entre conjoints : 50/50 (ou parts égales si plus de 2
     * personnes marquées "conjoint", cas rare mais géré par sécurité).
     */
    protected function repartirSeparation(float $solde, Collection $ayantsDroit): array
    {
        $conjoints = $ayantsDroit->where('role_succession', 'conjoint');

        if ($conjoints->isEmpty()) {
            $conjoints = $ayantsDroit;
        }

        $montantParConjoint = $solde / $conjoints->count();
        $repartitions = $conjoints->map(fn($c) => $this->ligne($c, 'Conjoint', 100 / $conjoints->count(), $montantParConjoint));

        return ['solde_disponible' => $solde, 'repartitions' => $repartitions, 'avertissement' => null];
    }

    /**
     * La famille définit elle-même les parts, via le pourcentage manuel
     * saisi sur chaque ayant droit. Avertit si la somme ne fait pas 100%.
     */
    protected function repartirPersonnalisee(float $solde, Collection $ayantsDroit): array
    {
        $totalPourcentage = $ayantsDroit->sum('pourcentage_manuel');

        $repartitions = $ayantsDroit->map(function ($ad) use ($solde) {
            $pourcentage = (float) ($ad->pourcentage_manuel ?? 0);
            return $this->ligne($ad, 'Personnalisé', $pourcentage, $solde * $pourcentage / 100);
        });

        $avertissement = null;
        if (round($totalPourcentage, 2) !== 100.0) {
            $avertissement = "⚠️ La somme des pourcentages saisis est de {$totalPourcentage}% (devrait être 100%). Vérifiez les parts manuelles de chaque ayant droit.";
        }

        return ['solde_disponible' => $solde, 'repartitions' => $repartitions, 'avertissement' => $avertissement];
    }

    protected function ligne($ayantDroit, string $roleLabel, float $pourcentage, float $montant): array
    {
        return [
            'ayant_droit' => $ayantDroit,
            'role_label' => $roleLabel,
            'part_pourcentage' => round($pourcentage, 2),
            'montant' => round($montant, 2),
        ];
    }
}
