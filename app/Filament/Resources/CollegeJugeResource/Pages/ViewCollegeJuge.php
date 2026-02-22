<?php

namespace App\Filament\Resources\CollegeJugeResource\Pages;

use App\Filament\Resources\CollegeJugeResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCollegeJuge extends ViewRecord
{
    protected static string $resource = CollegeJugeResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations du collège')
                    ->schema([
                        Infolists\Components\TextEntry::make('designation')
                            ->label('Désignation')
                            ->size('lg')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('tribunal.nom')
                            ->label('Tribunal')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('Aucune description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Composition')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('juges')
                            ->label('Membres du collège')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Juge')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('pivot.qualite')
                                    ->label('Qualité')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'president' => 'danger',
                                        'juge_1', 'juge_2' => 'info',
                                        'assesseur_1', 'assesseur_2' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'president' => 'Président',
                                        'juge_1' => 'Juge 1',
                                        'juge_2' => 'Juge 2',
                                        'assesseur_1' => 'Assesseur 1',
                                        'assesseur_2' => 'Assesseur 2',
                                        'juge_suppléant' => 'Juge suppléant',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('matricule')
                                    ->label('Matricule')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('grade')
                                    ->label('Grade')
                                    ->placeholder('Non renseigné'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
