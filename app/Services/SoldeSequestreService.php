<?php

namespace App\Services;

use App\Models\Sequestre;
use App\Models\MouvementSequestre;

class SoldeSequestreService
{
    /**
     * Calcule le précompte et le montant net d'un mouvement avant sauvegarde.
     * Reproduit : Montant séquestre = ENTREE × Taux ; Montant Réel = ENTREE - Montant séquestre
     */
    public function calculerMontants(MouvementSequestre $mouvement, Sequestre $sequestre): void
    {
        $mouvement->taux_applique = $sequestre->taux_precompte;

        if ($mouvement->type_mouvement === 'versement') {
            $mouvement->montant_precompte = round($mouvement->montant_mouvement * $sequestre->taux_precompte, 2);
            $mouvement->montant_net = $mouvement->montant_mouvement - $mouvement->montant_precompte;
        } else {
            $mouvement->montant_precompte = 0;
            $mouvement->montant_net = -$mouvement->montant_mouvement;
        }

        $dernierSolde = MouvementSequestre::where('sequestre_id', $sequestre->id)
            ->when($mouvement->exists, fn($q) => $q->where('id', '!=', $mouvement->id))
            ->latest('date_mouvement')
            ->latest('id')
            ->value('solde_apres');

        // ✅ Si aucun mouvement précédent, partir du fonds initial (pas 0)
        $mouvement->solde_apres = ($dernierSolde ?? (float) $sequestre->fonds_initial) + $mouvement->montant_net;
    }
    /**
     * Recalcule en cascade le solde_apres (SOLDE) de TOUS les mouvements
     * d'un séquestre, dans l'ordre chronologique — comme un grand livre.
     */
    public function recalculerSolde(Sequestre $sequestre): void
    {
        $mouvements = MouvementSequestre::where('sequestre_id', $sequestre->id)
            ->orderBy('date_mouvement')
            ->orderBy('id')
            ->get();

        // ✅ Le fonds initial est le point de départ du grand livre,
        // pas systématiquement 0.
        $soldeCourant = (float) $sequestre->fonds_initial;

        foreach ($mouvements as $mouvement) {
            $soldeCourant += (float) $mouvement->montant_net;

            MouvementSequestre::withoutEvents(function () use ($mouvement, $soldeCourant) {
                $mouvement->update(['solde_apres' => $soldeCourant]);
            });
        }
    }
}
