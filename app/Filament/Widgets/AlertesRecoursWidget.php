<?php

namespace App\Filament\Widgets;

use App\Models\AlerteRecours;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertesRecoursWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('🔔 Alertes Délais Recours')
            ->query(
                AlerteRecours::query()
                    ->where('est_lue', false)
                    ->whereHas('recours', function ($query) {
                        $query->where('statut_global', 'en_cours');
                    })
                    ->latest('date_declenchement')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('niveau')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'rouge' => 'danger',
                        'orange' => 'warning',
                        'jaune' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'rouge' => 'URGENT',
                        'orange' => 'ATTENTION',
                        'jaune' => 'INFO',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('recours.numero_recours')
                    ->label('Recours')
                    ->searchable()
                    ->url(fn($record) => route('filament.admin.resources.recours.view', $record->recours_id))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('date_declenchement')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('marquer_lue')
                    ->label('Marquer comme lue')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update([
                            'est_lue' => true,
                            'date_lecture' => now(),
                        ]);
                    }),
            ])
            ->emptyStateHeading('Aucune alerte')
            ->emptyStateDescription('Aucune alerte de délai en cours')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
