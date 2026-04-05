<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Recours;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertesRecoursWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('📋 Recours récents')
            ->description('Liste des derniers recours enregistrés')
            ->query(
                Recours::query()
                    ->with(['decision', 'typeRecours'])
                    ->whereNull('date_transmission_cour_appel') // Recours non encore transmis
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_recours')
                    ->label('N° Recours')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('decision.numero_repertoire')
                    ->label('Décision')
                    ->badge()
                    ->color('danger')
                    ->url(fn($record) => $record->decision_id
                        ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record->decision_id])
                        : null),

                Tables\Columns\TextColumn::make('type_recours')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'appel' => '⚖️ Appel',
                        'opposition' => '⚠️ Opposition',
                        'tierce_opposition' => '👥 Tierce opposition',
                        'retractation' => '🔄 Rétractation',
                        'revision' => '🔍 Révision',
                        'pourvoi_cassation' => '⚖️ Pourvoi',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'appel' => 'danger',
                        'opposition' => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('date_recours')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_enregistrement')
                    ->label('Enregistré')
                    ->date('d/m/Y')
                    ->badge()
                    ->color('success')
                    ->placeholder('En attente'),

                Tables\Columns\TextColumn::make('nombre_documents')
                    ->label('Docs')
                    ->getStateUsing(fn($record) => count($record->documents_mise_en_etat ?? []))
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\RecoursResource::getUrl('view', ['record' => $record]))
                    ->color('primary'),
            ])
            ->paginated([5, 10]);
    }
}