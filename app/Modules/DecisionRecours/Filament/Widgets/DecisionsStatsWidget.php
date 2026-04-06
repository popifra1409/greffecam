<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Decision;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DecisionsStatsWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = '📊 Répartition des décisions par statut';

    protected static ?string $description = 'Vue d\'ensemble du workflow';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Compter les décisions par statut
        $stats = Decision::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        // Labels et couleurs
        $labels = [];
        $data = [];
        $backgroundColors = [];

        $statutsConfig = [
            'brouillon' => ['label' => 'Brouillon', 'color' => 'rgb(156, 163, 175)'],
            'validee' => ['label' => 'Validée', 'color' => 'rgb(59, 130, 246)'],
            'saisie' => ['label' => 'Saisie', 'color' => 'rgb(139, 92, 246)'],
            'signee' => ['label' => 'Signée', 'color' => 'rgb(34, 197, 94)'],
            'enregistree' => ['label' => 'Enregistrée', 'color' => 'rgb(16, 185, 129)'],
            'archivee' => ['label' => 'Archivée', 'color' => 'rgb(107, 114, 128)'],
        ];

        foreach ($statutsConfig as $statut => $config) {
            $count = $stats[$statut] ?? 0;
            if ($count > 0) {
                $labels[] = $config['label'];
                $data[] = $count;
                $backgroundColors[] = $config['color'];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de décisions',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // ou 'pie' ou 'bar'
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}