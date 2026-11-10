@include('filament.partials.topbar-shortcuts', [
    'actions' => [
        [
            'label' => 'Nouveau dossier',
            'title' => 'Créer un nouveau dossier',
            'url' => \App\Modules\DecisionRecours\Filament\Resources\DossierResource::getUrl('create'),
            'icon' => 'heroicon-o-folder-plus',
            'visible' => \App\Modules\DecisionRecours\Filament\Resources\DossierResource::canCreate(),
        ],
        [
            'label' => 'Nouvelle décision',
            'title' => 'Créer une nouvelle décision',
            'url' => \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('create'),
            'icon' => 'heroicon-o-document-plus',
            'visible' => \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::canCreate(),
        ],
        [
            'label' => 'Recours en attente',
            'title' => 'Recours en attente de transmission',
            'url' => \App\Modules\DecisionRecours\Filament\Resources\RecoursResource::getUrl('index'),
            'icon' => 'heroicon-o-exclamation-triangle',
            'variant' => 'warning',
            'visible' => \App\Modules\DecisionRecours\Filament\Resources\RecoursResource::canViewAny()
                && ($recoursEnAttente = \App\Models\Recours::whereNull('date_transmission_cour_appel')->count()) > 0,
            'badge' => $recoursEnAttente ?? 0,
        ],
    ],
])
