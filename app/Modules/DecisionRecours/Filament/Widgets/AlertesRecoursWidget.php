<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\Recours;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertesRecoursWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚠️ Recours nécessitant une action')
            ->description('Recours déclarés en attente de traitement par le greffe')
            ->query(
                Recours::query()
                    ->with(['decision.dossier.tribunal', 'decision'])
                    ->where(function($query) {
                        // Recours non transmis depuis plus de 7 jours
                        $query->whereNull('date_transmission_cour_appel')
                            ->where('date_recours', '<=', now()->subDays(7))
                            // OU recours non enregistrés depuis plus de 3 jours
                            ->orWhere(function($q) {
                                $q->whereNull('date_enregistrement')
                                  ->where('created_at', '<=', now()->subDays(3));
                            });
                    })
                    ->orderByRaw("
                        CASE 
                            WHEN date_enregistrement IS NULL THEN 1
                            WHEN date_transmission_cour_appel IS NULL THEN 2
                            ELSE 3
                        END
                    ")
                    ->orderBy('date_recours', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('urgence')
                    ->label('')
                    ->getStateUsing(function($record) {
                        $joursDepuisDeclaration = now()->diffInDays($record->date_recours);
                        if ($joursDepuisDeclaration > 30) return '🔴';
                        if ($joursDepuisDeclaration > 14) return '🟠';
                        return '🔵';
                    })
                    ->size('lg')
                    ->tooltip(fn($record) => 'Déclaré il y a ' . now()->diffInDays($record->date_recours) . ' jours'),

                Tables\Columns\TextColumn::make('numero_recours')
                    ->label('N° Recours')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('type_recours')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'appel' => 'Appel',
                        'opposition' => 'Opposition',
                        'tierce_opposition' => 'Tierce opposition',
                        'retractation' => 'Rétractation',
                        'revision' => 'Révision',
                        'pourvoi_cassation' => 'Pourvoi',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'appel' => 'danger',
                        'opposition' => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('action_requise')
                    ->label('Action requise')
                    ->getStateUsing(function($record) {
                        if (!$record->date_enregistrement) {
                            return '📋 À enregistrer';
                        }
                        if (!$record->date_transmission_cour_appel) {
                            $joursDernierEnregistrement = $record->date_enregistrement 
                                ? now()->diffInDays($record->date_enregistrement)
                                : 0;
                            
                            if ($joursDernierEnregistrement > 14) {
                                return '🔥 URGENT : À transmettre à la CA';
                            }
                            return '📤 À transmettre à la CA';
                        }
                        return '✅ En cours';
                    })
                    ->badge()
                    ->color(fn($state) => match(true) {
                        str_contains($state, 'enregistrer') => 'danger',
                        str_contains($state, 'URGENT') => 'danger',
                        str_contains($state, 'transmettre') => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('date_recours')
                    ->label('Déclaré le')
                    ->date('d/m/Y')
                    ->description(fn($record) => 
                        now()->diffInDays($record->date_recours) . ' jours écoulés'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('decision.numero_repertoire')
                    ->label('Décision')
                    ->badge()
                    ->color('gray')
                    ->url(fn($record) => $record->decision_id 
                        ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record->decision_id])
                        : null)
                    ->tooltip('Cliquer pour voir la décision'),

                Tables\Columns\TextColumn::make('decision.dossier.tribunal.nom')
                    ->label('Tribunal')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('traiter')
                    ->label('Traiter')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\RecoursResource::getUrl('edit', ['record' => $record])),
                
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\RecoursResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10, 25])
            ->emptyStateHeading('✅ Aucun recours en attente')
            ->emptyStateDescription('Tous les recours déclarés sont à jour !')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}