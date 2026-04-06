<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Dossier;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DossiersRecentWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('📁 Dossiers récents')
            ->description('Les 10 derniers dossiers créés')
            ->query(
                Dossier::query()
                    ->with(['tribunal', 'section', 'matiere', 'anneeJudiciaire'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_dossier')
                    ->label('N° Dossier')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tribunal.nom')
                    ->label('Tribunal')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('section.libelle')
                    ->label('Section')
                    ->badge()
                    ->color(fn($record) => $record->section?->type === 'repressive' ? 'danger' : 'success')
                    ->icon(fn($record) => $record->section?->type === 'repressive' ? 'heroicon-o-shield-exclamation' : 'heroicon-o-scale'),

                Tables\Columns\TextColumn::make('matiere.designation')
                    ->label('Matière')
                    ->badge()
                    ->color('gray')
                    ->limit(30),

                Tables\Columns\TextColumn::make('demandeurs_count')
                    ->label('Demandeurs')
                    ->counts('demandeurs')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-user-group'),

                Tables\Columns\TextColumn::make('defendeurs_count')
                    ->label('Défendeurs')
                    ->counts('defendeurs')
                    ->badge()
                    ->color('purple')
                    ->icon('heroicon-o-user-group'),

                Tables\Columns\TextColumn::make('decisions_count')
                    ->label('Décisions')
                    ->counts('decisions')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('date_enrolement')
                    ->label('Enrôlé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(
                        fn($record) => $record->date_enrolement
                        ? now()->diffForHumans($record->date_enrolement)
                        : '-'
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\DossierResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Aucun dossier')
            ->emptyStateDescription('Les dossiers apparaîtront ici une fois créés')
            ->emptyStateIcon('heroicon-o-folder');
    }
}