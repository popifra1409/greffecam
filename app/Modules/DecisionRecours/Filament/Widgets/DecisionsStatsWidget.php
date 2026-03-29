<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Decision;
use App\Models\AnneeJudiciaire;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DecisionsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $anneeActive = AnneeJudiciaire::where('is_active', true)->first();

        $query = Decision::query();

        if ($anneeActive) {
            $query->where('annee_judiciaire_id', $anneeActive->id);
        }

        return [
            Stat::make('Total Décisions', $query->count())
                ->description($anneeActive ? "Année {$anneeActive->libelle}" : 'Toutes années')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Brouillon', $query->clone()->where('statut', 'brouillon')->count())
                ->description('En cours de rédaction')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('gray'),

            Stat::make('Transmises', $query->clone()->where('statut', 'transmise_chef')->count())
                ->description('En attente de signature')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('warning'),

            Stat::make('Signées', $query->clone()->where('statut', 'signee')->count())
                ->description('En attente d\'enregistrement')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('info'),

            Stat::make('Enregistrées', $query->clone()->where('statut', 'enregistree')->count())
                ->description('Décisions finalisées')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Annulées', $query->clone()->where('statut', 'annulee')->count())
                ->description('Décisions annulées')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
