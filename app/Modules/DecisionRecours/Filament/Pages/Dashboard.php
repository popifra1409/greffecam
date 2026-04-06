<?php

namespace App\Modules\DecisionRecours\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'modules.decision-recours.dashboard';

    protected static ?string $title = 'Tableau de Bord';

    public function getWidgets(): array
    {
        return [
            // Stats d'ensemble (en haut)
            \App\Modules\DecisionRecours\Filament\Widgets\StatsOverviewWidget::class,

            // Widgets tableaux
            \App\Modules\DecisionRecours\Filament\Widgets\DossiersRecentWidget::class,
            \App\Modules\DecisionRecours\Filament\Widgets\AlertesRecoursWidget::class,
            \App\Modules\DecisionRecours\Filament\Widgets\DecisionsRecentesWidget::class,

            // Graphique
            \App\Modules\DecisionRecours\Filament\Widgets\DecisionsStatsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }
}