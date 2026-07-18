<?php

namespace App\Modules\SequestreCaution\Filament\Widgets;

use App\Models\Sequestre;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SequestresRecentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('🔒 Séquestres récents')
            ->description('Les 10 derniers séquestres ouverts')
            ->query(
                Sequestre::query()
                    ->with(['dossier', 'decision', 'natureSequestre', 'statutSequestre'])
                    ->latest('date_ouverture')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->label('N° Dossier')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('intitule')
                    ->label('Intitulé')
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('natureSequestre.libelle')
                    ->label('Nature')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('statutSequestre.libelle')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statutSequestre?->couleur ?? 'gray'),

                Tables\Columns\TextColumn::make('date_ouverture')
                    ->label('Ouverture')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('solde_actuel')
                    ->label('Solde')
                    ->money('XAF')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Aucun séquestre')
            ->emptyStateDescription('Les séquestres apparaîtront ici une fois créés')
            ->emptyStateIcon('heroicon-o-lock-closed');
    }
}
