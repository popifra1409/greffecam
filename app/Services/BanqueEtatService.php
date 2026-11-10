<?php

namespace App\Services;

use App\Models\BanqueReleve;
use App\Models\MouvementSequestre;
use App\Models\Sequestre;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Situation bancaire des séquestres à une date d'arrêté.
 *
 * Conventions retenues :
 *  - le solde à la banque = dernier relevé connu + mouvements enregistrés après sa date ;
 *  - le solde au séquestre = dernier solde_apres à la date d'arrêté, sinon le fonds initial ;
 *  - l'intérêt est prorata temporis depuis l'ouverture du compte (formule A) ;
 *  - la TVA s'applique sur l'intérêt, pas sur le solde.
 */
class BanqueEtatService
{
    public function requete(string $dateArrete): Builder
    {
        $date = Carbon::parse($dateArrete)->toDateString();

        $dateDernierReleveSql = "(select br.date_releve from banque_releves br
            where br.sequestre_id = sequestres.id and br.date_releve <= '{$date}'
            order by br.date_releve desc, br.id desc limit 1)";

        return Sequestre::query()
            ->with(['decision', 'dossier', 'natureSequestre', 'statutSequestre', 'representant'])
            ->addSelect([
                // 1 si un relevé existe à la date d'arrêté, sinon null.
                'a_un_releve' => BanqueReleve::selectRaw('1')
                    ->whereColumn('banque_releves.sequestre_id', 'sequestres.id')
                    ->whereDate('banque_releves.date_releve', '<=', $date)
                    ->limit(1),

                'dernier_solde_releve' => BanqueReleve::select('banque_releves.solde_banque')
                    ->whereColumn('banque_releves.sequestre_id', 'sequestres.id')
                    ->whereDate('banque_releves.date_releve', '<=', $date)
                    ->orderByDesc('banque_releves.date_releve')
                    ->orderByDesc('banque_releves.id')
                    ->limit(1),

                // Mouvements postérieurs au dernier relevé, jusqu'à la date d'arrêté.
                'flux_apres_releve' => MouvementSequestre::selectRaw(
                    "coalesce(sum(case when type_mouvement = 'versement' then montant_mouvement else -montant_mouvement end), 0)"
                )
                    ->whereColumn('mouvements_sequestre.sequestre_id', 'sequestres.id')
                    ->whereDate('mouvements_sequestre.date_mouvement', '<=', $date)
                    ->whereRaw("mouvements_sequestre.date_mouvement > coalesce({$dateDernierReleveSql}, '1900-01-01')"),

                'total_entrees' => MouvementSequestre::selectRaw('coalesce(sum(montant_mouvement), 0)')
                    ->whereColumn('mouvements_sequestre.sequestre_id', 'sequestres.id')
                    ->where('type_mouvement', 'versement')
                    ->whereDate('mouvements_sequestre.date_mouvement', '<=', $date)
                    ->whereRaw('mouvements_sequestre.date_mouvement >= coalesce(sequestres.date_ouverture_compte, sequestres.date_ouverture)'),

                'total_sorties' => MouvementSequestre::selectRaw('coalesce(sum(montant_mouvement), 0)')
                    ->whereColumn('mouvements_sequestre.sequestre_id', 'sequestres.id')
                    ->where('type_mouvement', 'retrait')
                    ->whereDate('mouvements_sequestre.date_mouvement', '<=', $date)
                    ->whereRaw('mouvements_sequestre.date_mouvement >= coalesce(sequestres.date_ouverture_compte, sequestres.date_ouverture)'),

                'solde_sequestre_arrete' => MouvementSequestre::select('mouvements_sequestre.solde_apres')
                    ->whereColumn('mouvements_sequestre.sequestre_id', 'sequestres.id')
                    ->whereDate('mouvements_sequestre.date_mouvement', '<=', $date)
                    ->orderByDesc('mouvements_sequestre.date_mouvement')
                    ->orderByDesc('mouvements_sequestre.id')
                    ->limit(1),
            ]);
    }

    public function lignes(string $dateArrete): Collection
    {
        return $this->requete($dateArrete)
            ->get()
            ->map(fn(Sequestre $sequestre) => [
                'intitule' => $sequestre->intitule,
                'numero_dossier' => $sequestre->numero_dossier_sequestre,
                'numero_compte' => $sequestre->numero_compte_bancaire,
                'date_ouverture_compte' => $sequestre->date_ouverture_compte,
                'total_entrees' => (float) $sequestre->total_entrees,
                'total_sorties' => (float) $sequestre->total_sorties,
                'solde_banque' => $this->soldeBanque($sequestre),
                'solde_sequestre' => $this->soldeSequestre($sequestre),
                'statut_compte' => $this->statutCompte($sequestre),
                'etat_compte' => $this->etatCompte($sequestre),
                'montant_ecart' => $this->montantEcart($sequestre),
                'interet' => $this->interet($sequestre, $dateArrete),
                'tva' => $this->tva($sequestre, $dateArrete),
                'solde_banque_interet' => $this->soldeBanqueInteret($sequestre, $dateArrete),
            ]);
    }

    /**
     * Solde à la banque : dernier relevé + mouvements postérieurs.
     * Null tant qu'aucun relevé n'a été saisi (solde inconnu, pas zéro).
     */
    public function soldeBanque(Sequestre $sequestre): ?float
    {
        if (! $sequestre->a_un_releve) {
            return null;
        }

        return (float) $sequestre->dernier_solde_releve + (float) $sequestre->flux_apres_releve;
    }

    public function soldeSequestre(Sequestre $sequestre): float
    {
        return $sequestre->solde_sequestre_arrete !== null
            ? (float) $sequestre->solde_sequestre_arrete
            : (float) $sequestre->fonds_initial;
    }

    public function statutCompte(Sequestre $sequestre): string
    {
        return filled($sequestre->numero_compte_bancaire) ? 'Ouvert' : 'À ouvrir';
    }

    public function etatCompte(Sequestre $sequestre): string
    {
        $banque = $this->soldeBanque($sequestre);

        if ($banque === null) {
            return 'À renseigner';
        }

        $sequestreSolde = $this->soldeSequestre($sequestre);

        if (abs($banque - $sequestreSolde) < 0.01) {
            return 'Équilibré';
        }

        return $banque > $sequestreSolde ? 'À décaisser' : 'À renflouer';
    }

    public function montantEcart(Sequestre $sequestre): float
    {
        $banque = $this->soldeBanque($sequestre);

        if ($banque === null) {
            return 0.0;
        }

        return round(abs($banque - $this->soldeSequestre($sequestre)), 2);
    }

    /**
     * Formule A : solde banque × taux annuel × jours écoulés / 365,
     * les jours partant de la date d'ouverture du compte.
     */
    public function interet(Sequestre $sequestre, string $dateArrete): float
    {
        $banque = $this->soldeBanque($sequestre);

        if ($banque === null || ! $sequestre->date_ouverture_compte) {
            return 0.0;
        }

        $jours = Carbon::parse($sequestre->date_ouverture_compte)->startOfDay()
            ->diffInDays(Carbon::parse($dateArrete)->startOfDay(), false);

        $jours = max(0, (int) $jours);

        return round($banque * (float) config('banque.taux_interet_annuel') * $jours / 365, 2);
    }

    public function tva(Sequestre $sequestre, string $dateArrete): float
    {
        return round($this->interet($sequestre, $dateArrete) * (float) config('banque.taux_tva_interet'), 2);
    }

    public function soldeBanqueInteret(Sequestre $sequestre, string $dateArrete): ?float
    {
        $banque = $this->soldeBanque($sequestre);

        if ($banque === null) {
            return null;
        }

        return round($banque + $this->interet($sequestre, $dateArrete), 2);
    }
}
