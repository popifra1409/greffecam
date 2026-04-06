<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Dossier;
use App\Models\Decision;
use App\Models\Recours;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Statistiques dossiers
        $totalDossiers = Dossier::count();
        $dossiersActifs = Dossier::whereHas('decisions', function ($q) {
            $q->whereNotIn('statut', ['archivee']);
        })->count();
        $dossiersRecents = Dossier::where('created_at', '>=', now()->subDays(30))->count();

        // Statistiques décisions
        $totalDecisions = Decision::count();
        $decisionsEnCours = Decision::whereIn('statut', ['brouillon', 'validee', 'saisie'])->count();
        $decisionsSignees = Decision::where('statut', 'signee')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $decisionsEnregistrees = Decision::where('statut', 'enregistree')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Statistiques recours
        $totalRecours = Recours::count();
        $recoursEnAttente = Recours::whereNull('date_transmission_cour_appel')->count();
        $recoursRecents = Recours::where('created_at', '>=', now()->subDays(30))->count();

        // Taux de recours (pourcentage de décisions qui ont fait l'objet d'un recours)
        $decisionsAvecRecours = Decision::has('recours')->count();
        $tauxRecours = $totalDecisions > 0
            ? round(($decisionsAvecRecours / $totalDecisions) * 100, 1)
            : 0;

        return [
            // DOSSIERS
            Stat::make('Dossiers', $totalDossiers)
                ->description("{$dossiersActifs} actifs")
                ->descriptionIcon('heroicon-o-folder-open')
                ->chart([$dossiersRecents, $totalDossiers]) // Mini graphique
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.decision-recours.resources.dossiers.index')),

            // DÉCISIONS EN COURS
            Stat::make('Décisions en cours', $decisionsEnCours)
                ->description('Brouillon, validée, saisie')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('warning')
                ->url(route('filament.decision-recours.resources.decisions.index')),

            // DÉCISIONS SIGNÉES (ce mois)
            Stat::make('Décisions signées (30j)', $decisionsSignees)
                ->description("{$decisionsEnregistrees} enregistrées")
                ->descriptionIcon('heroicon-o-pencil-square')
                ->color('success')
                ->chart([$decisionsSignees, $decisionsEnregistrees])
                ->url(route('filament.decision-recours.resources.decisions.index', [
                    'tableFilters' => ['statut' => ['values' => ['signee']]],
                ])),

            // RECOURS
            Stat::make('Recours', $totalRecours)
                ->description("{$recoursEnAttente} en attente de transmission")
                ->descriptionIcon('heroicon-o-arrow-path-rounded-square')
                ->color($recoursEnAttente > 0 ? 'danger' : 'info')
                ->chart([$recoursRecents, $totalRecours])
                ->url(route('filament.decision-recours.resources.recours.index')),

            // TAUX DE RECOURS
            Stat::make('Taux de recours', "{$tauxRecours}%")
                ->description("{$decisionsAvecRecours} décisions avec recours")
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($tauxRecours > 20 ? 'warning' : 'success')
                ->url(route('filament.decision-recours.resources.recours.index')),

            // TOTAL DÉCISIONS
            Stat::make('Total décisions', $totalDecisions)
                ->description('Toutes les décisions')
                ->descriptionIcon('heroicon-o-scale')
                ->color('gray')
                ->url(route('filament.decision-recours.resources.decisions.index')),
        ];
    }
}