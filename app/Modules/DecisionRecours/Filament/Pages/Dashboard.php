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
            \App\Modules\DecisionRecours\Filament\Widgets\TransmissionsEnAttenteWidget::class,
            \App\Modules\DecisionRecours\Filament\Widgets\AlertesRecoursWidget::class,
            \App\Modules\DecisionRecours\Filament\Widgets\DecisionsStatsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}