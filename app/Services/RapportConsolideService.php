<?php

namespace App\Services;

use App\Models\MouvementSequestre;
use App\Models\Sequestre;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Requête et totaux du rapport consolidé des séquestres.
 *
 * Le solde affiché est arrêté à la date de fin de période (solde du dernier
 * mouvement à cette date). À défaut, il correspond au solde actuel. Les
 * séquestres sans mouvement retombent sur leur fonds initial, comme le
 * tableau de bord — c'est ce qui aligne les deux chiffres.
 */
class RapportConsolideService
{
    public function requete(
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?int $statutId = null,
        ?int $natureId = null,
    ): Builder {
        $dateDebut = $dateDebut ? Carbon::parse($dateDebut)->toDateString() : null;
        $dateFin = $dateFin ? Carbon::parse($dateFin)->toDateString() : null;

        $filtreDates = function (Builder $query) use ($dateDebut, $dateFin) {
            if ($dateDebut) {
                $query->whereDate('date_mouvement', '>=', $dateDebut);
            }
            if ($dateFin) {
                $query->whereDate('date_mouvement', '<=', $dateFin);
            }
        };

        $conditionSolde = $dateFin ? ' and m.date_mouvement <= ?' : '';
        $soldeArreteSql = 'coalesce((select m.solde_apres from mouvements_sequestre m'
            . ' where m.sequestre_id = sequestres.id' . $conditionSolde
            . ' order by m.date_mouvement desc, m.id desc limit 1), sequestres.fonds_initial)';

        // Solde avant le premier mouvement de la période : dernier solde arrêté
        // strictement avant le début, sinon fonds initial.
        $soldeOuvertureSql = $dateDebut
            ? 'coalesce((select m.solde_apres from mouvements_sequestre m'
                . ' where m.sequestre_id = sequestres.id and m.date_mouvement < ?'
                . ' order by m.date_mouvement desc, m.id desc limit 1), sequestres.fonds_initial)'
            : 'sequestres.fonds_initial';

        return Sequestre::query()
            ->with(['natureSequestre', 'statutSequestre'])
            ->when($statutId, fn(Builder $q) => $q->where('statut_sequestre_id', $statutId))
            ->when($natureId, fn(Builder $q) => $q->where('nature_sequestre_id', $natureId))
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
            ])
            ->selectRaw("{$soldeOuvertureSql} as solde_ouverture", $dateDebut ? [$dateDebut] : [])
            ->selectRaw("{$soldeArreteSql} as solde_arrete", $dateFin ? [$dateFin] : []);
    }

    public function totaux(Collection $sequestres): array
    {
        return [
            'totalEntrees' => (float) $sequestres->sum('total_entrees_periode'),
            'totalSorties' => (float) $sequestres->sum('total_sorties_periode'),
            'totalPrecompte' => (float) $sequestres->sum('total_precompte_periode'),
            'totalOuverture' => (float) $sequestres->sum('solde_ouverture'),
            'totalSolde' => (float) $sequestres->sum('solde_arrete'),
        ];
    }

    public function libelleOuverture(?string $dateDebut): string
    {
        return $dateDebut
            ? 'Solde au ' . Carbon::parse($dateDebut)->subDay()->format('d/m/Y')
            : 'Fonds initial';
    }

    public function libelleSolde(?string $dateFin): string
    {
        return $dateFin
            ? 'Solde au ' . Carbon::parse($dateFin)->format('d/m/Y')
            : 'Solde actuel';
    }
}
