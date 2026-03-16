<?php

namespace App\Filament\Resources\GreffierResource\Pages;

use App\Filament\Resources\GreffierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewGreffier extends ViewRecord
{
    protected static string $resource = GreffierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations personnelles')
                    ->schema([
                        Infolists\Components\TextEntry::make('matricule')
                            ->label('Matricule')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('nom_complet')
                            ->label('Nom complet')
                            ->getStateUsing(fn($record) => $record->nom_complet)
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('grade')
                            ->label('Grade')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('fonction')
                            ->label('Fonction')
                            ->getStateUsing(fn($record) => $record->fonction)
                            ->badge()
                            ->color(fn($record) => $record->est_chef ? 'warning' : 'gray'),

                        Infolists\Components\TextEntry::make('tribunal.nom')
                            ->label('Tribunal')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('telephone')
                            ->label('Téléphone')
                            ->icon('heroicon-o-phone')
                            ->placeholder('Non renseigné'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Statut')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])->columns(3),

                Infolists\Components\Section::make('Affectations')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('sections')
                            ->label('Sections affectées')
                            ->schema([
                                Infolists\Components\TextEntry::make('libelle')
                                    ->label('Section')
                                    ->badge()
                                    ->color(fn($record) => $record->type === 'repressive' ? 'danger' : 'success'),

                                Infolists\Components\TextEntry::make('code')
                                    ->label('Code')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn($state) => $state === 'repressive' ? 'Répressive' : 'Non Répressive')
                                    ->badge(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Statistiques')
                    ->schema([
                        Infolists\Components\TextEntry::make('decisions_count')
                            ->label('Décisions enregistrées')
                            ->getStateUsing(fn($record) => $record->decisions()->count())
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('dossiers_count')
                            ->label('Dossiers enrôlés')
                            ->getStateUsing(fn($record) => $record->dossiers()->count())
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y à H:i'),
                    ])->columns(4),
            ]);
    }
}
