@include('filament.partials.topbar-shortcuts', [
    'actions' => [
        [
            'label' => 'Nouveau séquestre',
            'title' => 'Créer un nouveau séquestre',
            'url' => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl('create'),
            'icon' => 'heroicon-o-lock-closed',
            'visible' => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::canCreate(),
        ],
        [
            'label' => 'État des comptes',
            'title' => 'Consulter l\'état des comptes bancaires',
            'url' => \App\Modules\SequestreCaution\Filament\Pages\BanqueEtat::getUrl(),
            'icon' => 'heroicon-o-banknotes',
            'visible' => \App\Modules\SequestreCaution\Filament\Pages\BanqueEtat::canAccess(),
        ],
        [
            'label' => 'Rapport consolidé',
            'title' => 'Consulter le rapport consolidé',
            'url' => \App\Modules\SequestreCaution\Filament\Pages\RapportConsolide::getUrl(),
            'icon' => 'heroicon-o-chart-bar-square',
            'visible' => \App\Modules\SequestreCaution\Filament\Pages\RapportConsolide::canAccess(),
        ],
    ],
])
