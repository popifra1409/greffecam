<?php

namespace App\Modules\SequestreCaution\Filament\Widgets;

use App\Models\Sequestre;
use App\Models\MouvementSequestre;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalSequestres = Sequestre::count();

        $sequestresActifs = Sequestre::whereHas('statutSequestre', function ($q) {
            $q->where('bloque_mouvements', false);
        })->count();

        $sequestresClotures = Sequestre::whereHas('statutSequestre', function ($q) {
            $q->where('bloque_mouvements', true);
        })->count();

        // Solde total = somme des derniers solde_apres de chaque séquestre
        $soldeTotal = Sequestre::with('mouvements')
            ->get()
            ->sum(fn($s) => $s->solde_actuel);

        $mouvementsCeMois = MouvementSequestre::whereMonth('date_mouvement', now()->month)
            ->whereYear('date_mouvement', now()->year)
            ->count();

        $totalPrecompteMois = MouvementSequestre::whereMonth('date_mouvement', now()->month)
            ->whereYear('date_mouvement', now()->year)
            ->sum('montant_precompte');

        return [
            Stat::make('Séquestres actifs', $sequestresActifs)
                ->description($totalSequestres . ' au total')
                ->descriptionIcon('heroicon-o-lock-open')
                ->color('success')
                ->url(route('filament.sequestre-caution.resources.sequestres.index')),

            Stat::make('Séquestres clôturés', $sequestresClotures)
                ->description('Statut bloquant les mouvements')
                ->descriptionIcon('heroicon-o-lock-closed')
                ->color('gray'),

            Stat::make('Solde total', number_format($soldeTotal, 0, ',', ' ') . ' FCFA')
                ->description('Cumul de tous les séquestres')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($soldeTotal >= 0 ? 'success' : 'danger'),

            Stat::make('Mouvements (ce mois)', $mouvementsCeMois)
                ->description('Versements et retraits')
                ->descriptionIcon('heroicon-o-arrows-right-left')
                ->color('info'),

            Stat::make('Précompte du mois', number_format($totalPrecompteMois, 0, ',', ' ') . ' FCFA')
                ->description('Montants précomptés en ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('warning'),
        ];
    }
}
