<?php

namespace App\Modules\SequestreCaution\Filament\Widgets;

use App\Models\Sequestre;
use App\Models\MouvementSequestre;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AlertesSequestresWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    /**
     * ✅ Seuil configurable : en dessous de ce montant (et > 0), le séquestre
     * est signalé comme "solde faible". À 0 ou moins, il est signalé comme
     * "candidat à la clôture" (plus rien à gérer, mais dossier encore ouvert).
     */
    protected static int $seuilSoldeBas = 20000;

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚠️ Alertes Séquestres')
            ->description('Soldes faibles ou épuisés nécessitant une attention (clôture éventuelle)')
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('alerte')
                    ->label('')
                    ->getStateUsing(fn($record) => $record->solde_courant <= 0 ? '🔴' : '🟠')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('numero_dossier_sequestre')
                    ->label('N° Dossier Séquestre')
                    ->badge()
                    ->color('primary')
                    ->url(fn($record) => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl('view', ['record' => $record])),

                Tables\Columns\TextColumn::make('intitule')
                    ->label('Intitulé')
                    ->wrap(),

                Tables\Columns\TextColumn::make('statutSequestre.libelle')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statutSequestre?->couleur ?? 'gray'),

                Tables\Columns\TextColumn::make('solde_courant')
                    ->label('Solde actuel')
                    ->money('XAF')
                    ->weight('bold')
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('type_alerte')
                    ->label('Alerte')
                    ->getStateUsing(fn($record) => $record->solde_courant <= 0
                        ? 'Solde épuisé — candidat à la clôture'
                        : 'Solde faible (< ' . number_format(static::$seuilSoldeBas, 0, ',', ' ') . ' FCFA)')
                    ->badge()
                    ->color(fn($record) => $record->solde_courant <= 0 ? 'danger' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('✅ Aucune alerte')
            ->emptyStateDescription('Tous les séquestres actifs ont un solde suffisant.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    protected function getQuery(): Builder
    {
        $soldeSubQuery = MouvementSequestre::selectRaw('solde_apres')
            ->whereColumn('sequestre_id', 'sequestres.id')
            ->orderByDesc('date_mouvement')
            ->orderByDesc('id')
            ->limit(1);

        return Sequestre::query()
            ->whereHas('statutSequestre', fn($q) => $q->where('bloque_mouvements', false))
            ->addSelect(['solde_courant' => $soldeSubQuery])
            ->whereRaw('(
                select coalesce(m.solde_apres, 0)
                from mouvements_sequestre m
                where m.sequestre_id = sequestres.id
                order by m.date_mouvement desc, m.id desc
                limit 1
            ) < ?', [static::$seuilSoldeBas])
            ->orderByRaw('(
                select coalesce(m.solde_apres, 0)
                from mouvements_sequestre m
                where m.sequestre_id = sequestres.id
                order by m.date_mouvement desc, m.id desc
                limit 1
            ) asc');
    }
}
