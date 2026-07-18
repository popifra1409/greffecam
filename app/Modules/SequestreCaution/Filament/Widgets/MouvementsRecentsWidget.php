<?php

namespace App\Modules\SequestreCaution\Filament\Widgets;

use App\Models\MouvementSequestre;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MouvementsRecentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('💰 Derniers mouvements')
            ->description('Les 10 derniers versements et retraits enregistrés')
            ->query(
                MouvementSequestre::query()
                    ->with(['sequestre.dossier', 'motifMouvement'])
                    ->latest('date_mouvement')
                    ->latest('id')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('date_mouvement')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sequestre.dossier.numero_dossier')
                    ->label('Dossier')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('operateur_beneficiaire')
                    ->label('Opérateur / Bénéficiaire')
                    ->wrap()
                    ->limit(25),

                Tables\Columns\TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn($record) => $record->type_mouvement === 'versement' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('motifMouvement.libelle')
                    ->label('Motif')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('montant_mouvement')
                    ->label('Montant')
                    ->money('XAF')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('solde_apres')
                    ->label('Solde après')
                    ->money('XAF')
                    ->weight('bold')
                    ->alignEnd()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('voir_sequestre')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl('view', ['record' => $record->sequestre_id])),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Aucun mouvement')
            ->emptyStateDescription('Les mouvements apparaîtront ici une fois enregistrés')
            ->emptyStateIcon('heroicon-o-arrows-right-left');
    }
}
