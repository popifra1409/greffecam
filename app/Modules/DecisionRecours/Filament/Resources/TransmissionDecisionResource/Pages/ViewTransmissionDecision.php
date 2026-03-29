<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TransmissionDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TransmissionDecisionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewTransmissionDecision extends ViewRecord
{
    protected static string $resource = TransmissionDecisionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Détails de la transmission')
                    ->schema([
                        Infolists\Components\TextEntry::make('decision.numero_rg')
                            ->label('Décision')
                            ->badge()
                            ->color('primary')
                            ->url(fn($record) => route('filament.admin.resources.decisions.view', $record->decision_id)),

                        Infolists\Components\TextEntry::make('expediteur.name')
                            ->label('Expéditeur')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('destinataire.name')
                            ->label('Destinataire')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('motif')
                            ->label('Motif')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'validation' => 'Validation',
                                'signature' => 'Signature',
                                'correction' => 'Correction',
                                'avis' => 'Avis',
                                'information' => 'Information',
                                'autre' => 'Autre',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'en_attente' => 'warning',
                                'acceptee' => 'success',
                                'rejetee' => 'danger',
                                'retournee' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'en_attente' => 'En attente',
                                'acceptee' => 'Acceptée',
                                'rejetee' => 'Rejetée',
                                'retournee' => 'Retournée',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('date_transmission')
                            ->label('Date de transmission')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_traitement')
                            ->label('Date de traitement')
                            ->dateTime('d/m/Y à H:i')
                            ->placeholder('Non traitée')
                            ->icon('heroicon-o-calendar'),
                    ])->columns(3),

                Infolists\Components\Section::make('Observations')
                    ->schema([
                        Infolists\Components\TextEntry::make('observations_expediteur')
                            ->label('Observations de l\'expéditeur')
                            ->markdown()
                            ->placeholder('Aucune observation')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('observations_destinataire')
                            ->label('Réponse du destinataire')
                            ->markdown()
                            ->placeholder('Pas encore traitée')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
