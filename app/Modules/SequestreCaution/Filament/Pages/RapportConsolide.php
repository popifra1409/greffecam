<?php

namespace App\Modules\SequestreCaution\Filament\Pages;

use App\Models\Sequestre;
use App\Models\MouvementSequestre;
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

    protected static ?string $navigationGroup = 'Gestion des Séquestres';

    protected static ?string $navigationLabel = 'Rapport Consolidé';

    protected static ?string $title = 'Rapport Consolidé des Séquestres';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'sequestres.pages.rapport-consolide';

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exporter_pdf')
                ->label('📊 Exporter en PDF')
                ->color('primary')
                ->url(fn() => $this->getExportUrl())
                ->openUrlInNewTab(),
        ];
    }

    protected function getExportUrl(): string
    {
        $filters = $this->tableFilters['periode'] ?? [];

        return route('sequestres.rapport-consolide.pdf', [
            'date_debut' => $filters['date_debut'] ?? null,
            'date_fin' => $filters['date_fin'] ?? null,
            'statut_sequestre_id' => $this->tableFilters['statut_sequestre_id']['value'] ?? null,
            'nature_sequestre_id' => $this->tableFilters['nature_sequestre_id']['value'] ?? null,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
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
                    ->label('Précompte (période)')
                    ->money('XAF')
                    ->color('warning')
                    ->alignEnd()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total')),

                Tables\Columns\TextColumn::make('solde_courant')
                    ->label('Solde actuel')
                    ->money('XAF')
                    ->weight('bold')
                    ->alignEnd()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('XAF')->label('Total général')),
            ])
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_debut')
                            ->label('Du')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        \Filament\Forms\Components\DatePicker::make('date_fin')
                            ->label('Au')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(fn(Builder $query) => $query) // le filtrage réel se fait dans getBaseQuery() via les sous-requêtes
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_debut'] ?? null) {
                            $indicators[] = 'Du ' . \Carbon\Carbon::parse($data['date_debut'])->format('d/m/Y');
                        }
                        if ($data['date_fin'] ?? null) {
                            $indicators[] = 'Au ' . \Carbon\Carbon::parse($data['date_fin'])->format('d/m/Y');
                        }
                        return $indicators;
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
     * Requête de base avec sous-requêtes calculant les totaux de la période
     * filtrée (entrées/sorties/précompte) et le solde courant (toujours global,
     * car c'est un solde de grand livre, indépendant de la période affichée).
     */
    protected function getBaseQuery(): Builder
    {
        $dateDebut = $this->tableFilters['periode']['date_debut'] ?? null;
        $dateFin = $this->tableFilters['periode']['date_fin'] ?? null;

        $filtreDates = function ($query) use ($dateDebut, $dateFin) {
            if ($dateDebut) {
                $query->whereDate('date_mouvement', '>=', $dateDebut);
            }
            if ($dateFin) {
                $query->whereDate('date_mouvement', '<=', $dateFin);
            }
        };

        return Sequestre::query()
            ->with(['natureSequestre', 'statutSequestre'])
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

                'solde_courant' => MouvementSequestre::selectRaw('solde_apres')
                    ->whereColumn('sequestre_id', 'sequestres.id')
                    ->orderByDesc('date_mouvement')
                    ->orderByDesc('id')
                    ->limit(1),
            ]);
    }
}
