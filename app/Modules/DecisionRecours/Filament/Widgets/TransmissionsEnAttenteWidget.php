<?php

namespace App\Modules\DecisionRecours\Filament\Widgets;

use App\Models\TransmissionDecision;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TransmissionsEnAttenteWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('📨 Transmissions en attente de traitement')
            ->query(
                TransmissionDecision::query()
                    ->where('destinataire_id', auth()->id())
                    ->where('statut', 'en_attente')
                    ->latest('date_transmission')
            )
            ->columns([
                Tables\Columns\TextColumn::make('decision.numero_rg')
                    ->label('Décision')
                    ->url(fn($record) => route('filament.admin.resources.decisions.view', $record->decision_id))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('expediteur.name')
                    ->label('De')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('motif')
                    ->label('Motif')
                    ->badge(),

                Tables\Columns\TextColumn::make('observations_expediteur')
                    ->label('Observations')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('date_transmission')
                    ->label('Transmise le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Aucune transmission en attente')
            ->emptyStateDescription('Vous n\'avez aucune transmission à traiter')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
