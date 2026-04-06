<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Decision;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DecisionsRecentesWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚖️ Décisions récentes')
            ->description('Les 10 dernières décisions')
            ->query(
                Decision::query()
                    ->with(['dossier.tribunal', 'natureDecision'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_repertoire')
                    ->label('N° Répertoire')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->label('Dossier')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'brouillon' => 'Brouillon',
                        'validee' => 'Validée',
                        'saisie' => 'Saisie',
                        'signee' => 'Signée',
                        'enregistree' => 'Enregistrée',
                        'archivee' => 'Archivée',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'brouillon' => 'gray',
                        'validee' => 'info',
                        'saisie' => 'purple',
                        'signee' => 'success',
                        'enregistree' => 'success',
                        'archivee' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'brouillon' => 'heroicon-o-pencil',
                        'validee' => 'heroicon-o-check-circle',
                        'saisie' => 'heroicon-o-document',
                        'signee' => 'heroicon-o-pencil-square',
                        'enregistree' => 'heroicon-o-archive-box',
                        'archivee' => 'heroicon-o-archive-box-x-mark',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('natureDecision.libelle')
                    ->label('Nature')
                    ->badge()
                    ->color('warning')
                    ->limit(20),

                Tables\Columns\TextColumn::make('date_decision')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(
                        fn($record) => $record->date_decision
                        ? now()->diffForHumans($record->date_decision)
                        : '-'
                    ),

                Tables\Columns\IconColumn::make('has_recours')
                    ->label('Recours')
                    ->getStateUsing(fn($record) => $record->recours()->exists())
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('dossier.tribunal.nom')
                    ->label('Tribunal')
                    ->badge()
                    ->color('info')
                    ->limit(25)
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Aucune décision')
            ->emptyStateDescription('Les décisions apparaîtront ici une fois créées')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}