<?php

namespace App\Modules\SequestreCaution\Filament\Pages;

use App\Filament\Filters\PeriodeFilter;
use App\Models\MouvementSequestre;
use App\Services\RapportConsolideService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class RapportConsolide extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Rapports';

    protected static ?string $navigationLabel = 'Rapport consolidé';

    protected static ?string $title = 'Rapport Consolidé des Séquestres';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'sequestres.pages.rapport-consolide';

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exporter_pdf')
                ->label('📊 Exporter en PDF')
                ->color('primary')
                ->url(fn() => $this->getExportUrl())
                ->openUrlInNewTab()
                ->tooltip('Le PDF reprend la période et les filtres actifs (statut, nature).'),
        ];
    }

    protected function getExportUrl(): string
    {
        return route('sequestres.rapport-consolide.pdf', array_filter([
            'date_debut' => $this->dateDebutFiltre(),
            'date_fin' => $this->dateFinFiltre(),
            'statut_sequestre_id' => $this->tableFilters['statut_sequestre_id']['value'] ?? null,
            'nature_sequestre_id' => $this->tableFilters['nature_sequestre_id']['value'] ?? null,
        ], fn($valeur) => filled($valeur)));
    }

    protected function dateDebutFiltre(): ?string
    {
        return $this->tableFilters['periode']['date_debut'] ?? null;
    }

    protected function dateFinFiltre(): ?string
    {
        return $this->tableFilters['periode']['date_fin'] ?? null;
    }

    /**
     * Rappel visible de la période active, au-dessus du tableau.
     */
    public function getSubheading(): ?string
    {
        $debut = $this->dateDebutFiltre();
        $fin = $this->dateFinFiltre();

        if (! $debut && ! $fin) {
            return 'Toutes périodes confondues — soldes actuels';
        }

        return 'Période : ' . ($debut ? Carbon::parse($debut)->format('d/m/Y') : 'origine')
            . ' → ' . ($fin ? Carbon::parse($fin)->format('d/m/Y') : 'aujourd\'hui')
            . ' — soldes arrêtés au ' . ($fin ? Carbon::parse($fin)->format('d/m/Y') : Carbon::today()->format('d/m/Y'));
    }

    public function table(Table $table): Table
    {
        return $table
            // ✅ Requête construite à la lecture des lignes (et non à la
            // construction du tableau) : les dates du filtre sont donc prises
            // en compte dès le premier changement, sans rechargement.
            ->query(fn(): Builder => $this->getBaseQuery())
            ->columns([
                Tables\Columns\TextColumn::make('numero_dossier_sequestre')
                    ->label('N° Dossier Séquestre')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('intitule')
                    ->label('Intitulé')
                    ->wrap(),

                Tables\Columns\TextColumn::make('natureSequestre.libelle')
                    ->label('Nature')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('statutSequestre.libelle')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statutSequestre?->couleur ?? 'gray'),

                Tables\Columns\TextColumn::make('total_entrees_periode')
                    ->label('Entrées (période)')
                    ->money('XAF')
                    ->color('success')
                    ->alignEnd()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total')),

                Tables\Columns\TextColumn::make('total_sorties_periode')
                    ->label('Sorties (période)')
                    ->money('XAF')
                    ->color('danger')
                    ->alignEnd()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total')),

                Tables\Columns\TextColumn::make('total_precompte_periode')
                    ->label('Montant Séquestre (période)')
                    ->money('XAF')
                    ->color('warning')
                    ->alignEnd()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total')),

                Tables\Columns\TextColumn::make('solde_ouverture')
                    ->label(fn(): string => app(RapportConsolideService::class)->libelleOuverture($this->dateDebutFiltre()))
                    ->money('XAF')
                    ->alignEnd()
                    ->color('gray')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total')),

                Tables\Columns\TextColumn::make('solde_arrete')
                    ->label(fn(): string => app(RapportConsolideService::class)->libelleSolde($this->dateFinFiltre()))
                    ->money('XAF')
                    ->weight('bold')
                    ->alignEnd()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total général')),
            ])
            ->filters([
                // ✅ Période : une année remplit les deux dates, et une date
                // effacée revient à « toutes périodes ». Les champs sont live
                // pour que le tableau et les totaux suivent immédiatement.
                Tables\Filters\Filter::make('periode')
                    ->label('Période')
                    ->form([
                        \Filament\Forms\Components\Select::make('annee')
                            ->label('Année')
                            ->placeholder('Toutes les périodes')
                            ->options(fn(): array => PeriodeFilter::anneesDisponibles(
                                MouvementSequestre::class,
                                'date_mouvement',
                            ))
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('date_debut', filled($state) ? "{$state}-01-01" : null);
                                $set('date_fin', filled($state) ? "{$state}-12-31" : null);
                            })
                            ->helperText('Choisir une année remplit les deux dates ; vous pouvez ensuite les affiner.'),

                        \Filament\Forms\Components\DatePicker::make('date_debut')
                            ->label('Du')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            // Une date saisie à la main ne correspond plus à
                            // l'année entière : on vide le raccourci « Année ».
                            ->afterStateUpdated(fn(callable $set) => $set('annee', null)),

                        \Filament\Forms\Components\DatePicker::make('date_fin')
                            ->label('Au')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('annee', null)),
                    ])
                    // La période alimente les totaux et les soldes dans
                    // getBaseQuery(), elle ne masque aucun séquestre.
                    ->indicateUsing(function (array $data): array {
                        $debut = $data['date_debut'] ?? null;
                        $fin = $data['date_fin'] ?? null;

                        if (! $debut && ! $fin) {
                            return [];
                        }

                        return [
                            'Période : ' . ($debut ? Carbon::parse($debut)->format('d/m/Y') : 'origine')
                                . ' → ' . ($fin ? Carbon::parse($fin)->format('d/m/Y') : 'aujourd\'hui'),
                        ];
                    }),

                Tables\Filters\SelectFilter::make('statut_sequestre_id')
                    ->label('Statut')
                    ->relationship('statutSequestre', 'libelle'),

                Tables\Filters\SelectFilter::make('nature_sequestre_id')
                    ->label('Nature')
                    ->relationship('natureSequestre', 'libelle'),
            ])
            ->defaultSort('numero_dossier_sequestre');
    }

    /**
     * Requête de base : totaux de la période filtrée et solde arrêté à la
     * date de fin (ou solde actuel), avec repli sur le fonds initial pour
     * les séquestres sans mouvement.
     */
    protected function getBaseQuery(): Builder
    {
        return app(RapportConsolideService::class)->requete(
            dateDebut: $this->dateDebutFiltre(),
            dateFin: $this->dateFinFiltre(),
            statutId: $this->tableFilters['statut_sequestre_id']['value'] ?? null,
            natureId: $this->tableFilters['nature_sequestre_id']['value'] ?? null,
        );
    }
}
