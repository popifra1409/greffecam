<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCollegeJuge extends ViewRecord
{
    protected static string $resource = CollegeJugeResource::class;

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
                Infolists\Components\Section::make('Informations du collège')
                    ->schema([
                        Infolists\Components\TextEntry::make('designation')
                            ->label('Désignation')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('tribunal.nom')
                            ->label('Tribunal')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('Aucune description'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Actif')
                            ->boolean(),
                    ])->columns(2),

                Infolists\Components\Section::make('Composition du collège')
                    ->description(fn($record) => 'Ce collège est composé de ' . $record->juges->count() . ' juge(s)')
                    ->schema([
                        // ✅ Utiliser 'juges' pas 'membres'
                        Infolists\Components\RepeatableEntry::make('juges')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Identité')
                                    ->getStateUsing(fn($record) => $record->nom_complet)
                                    ->badge()
                                    ->color('primary')
                                    ->weight('bold')
                                    ->icon('heroicon-o-user'),

                                Infolists\Components\TextEntry::make('pivot.qualite')
                                    ->label('Qualité')
                                    ->badge()
                                    ->color('warning')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'president' => 'Président',
                                        'membre' => 'Membre',
                                        'assesseur' => 'Assesseur',
                                        default => ucfirst($state),
                                    }),

                                Infolists\Components\TextEntry::make('grade')
                                    ->label('Grade')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('Non renseigné'),

                                Infolists\Components\TextEntry::make('matricule')
                                    ->label('Matricule')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('Non renseigné'),

                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->placeholder('Non renseigné')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('telephone')
                                    ->label('Téléphone')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('Non renseigné')
                                    ->copyable(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Métadonnées')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y à H:i'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}