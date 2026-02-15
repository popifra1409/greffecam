<?php

namespace App\Filament\Resources\RecoursResource\Pages;

use App\Filament\Resources\RecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewRecours extends ViewRecord
{
    protected static string $resource = RecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('etape_suivante')
                ->label('Passer à l\'étape suivante')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn() => $this->record->statut_global === 'en_cours' && $this->record->etape_actuelle < 11)
                ->requiresConfirmation()
                ->modalHeading('Passer à l\'étape suivante')
                ->modalDescription(fn() => 'Voulez-vous compléter l\'étape ' . $this->record->etape_actuelle . ' et passer à l\'étape suivante ?')
                ->action(function () {
                    $this->record->passerEtapeSuivante();

                    \Filament\Notifications\Notification::make()
                        ->title('Étape complétée')
                        ->body('Passage à l\'étape ' . $this->record->etape_actuelle)
                        ->success()
                        ->send();

                    $this->refreshFormData([
                        'etape_actuelle',
                        'statut_global',
                    ]);
                }),

            Actions\Action::make('marquer_recevabilite')
                ->label('Marquer la recevabilité')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->visible(fn() => $this->record->statut_recevabilite === 'en_cours_examen')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->marquerRecevabilite();

                    \Filament\Notifications\Notification::make()
                        ->title('Recevabilité déterminée')
                        ->body('Le recours est ' . ($this->record->statut_recevabilite === 'recevable' ? 'recevable' : 'irrecevable'))
                        ->success()
                        ->send();

                    $this->refreshFormData([
                        'statut_recevabilite',
                        'motif_irrecevabilite',
                    ]);
                }),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Identification du recours')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_recours')
                            ->label('Numéro du recours')
                            ->copyable()
                            ->badge()
                            ->color('primary')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('decision.numero_rg')
                            ->label('Décision attaquée')
                            ->badge()
                            ->url(fn($record) => $record->decision_id ? route('filament.admin.resources.decisions.view', $record->decision_id) : null)
                            ->color('info'),

                        Infolists\Components\TextEntry::make('typeRecours.libelle')
                            ->label('Type de recours')
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('anneeJudiciaire.libelle')
                            ->label('Année judiciaire')
                            ->badge(),
                    ])->columns(4),

                Infolists\Components\Section::make('Parties au recours')
                    ->schema([
                        Infolists\Components\TextEntry::make('appelant')
                            ->label('Appelant / Requérant')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('intime')
                            ->label('Intimé / Défendeur')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),
                    ])->columns(2),

                Infolists\Components\Section::make('Dates et délais')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_decision_attaquee')
                            ->label('Date de la décision')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_interjetee')
                            ->label('Date d\'interjection')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_limite_recours')
                            ->label('Date limite légale')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar')
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('date_notification')
                            ->label('Date de notification')
                            ->date('d/m/Y')
                            ->placeholder('Non notifié')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('jours_restants')
                            ->label('Délai restant')
                            ->badge()
                            ->getStateUsing(fn($record) => $record->jours_restants . ' jours ouvrables')
                            ->color(fn($record) => match ($record->niveau_alerte) {
                                'rouge' => 'danger',
                                'orange' => 'warning',
                                'jaune' => 'info',
                                default => 'success',
                            }),

                        Infolists\Components\TextEntry::make('niveau_alerte')
                            ->label('Niveau d\'alerte')
                            ->badge()
                            ->getStateUsing(fn($record) => match ($record->niveau_alerte) {
                                'rouge' => 'URGENT (H-48)',
                                'orange' => 'Attention (J-7)',
                                'jaune' => 'À surveiller (J-15)',
                                default => 'Normal',
                            })
                            ->color(fn($record) => match ($record->niveau_alerte) {
                                'rouge' => 'danger',
                                'orange' => 'warning',
                                'jaune' => 'info',
                                default => 'success',
                            }),
                    ])->columns(3),

                Infolists\Components\Section::make('Recevabilité')
                    ->schema([
                        Infolists\Components\TextEntry::make('statut_recevabilite')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'recevable' => 'success',
                                'irrecevable' => 'danger',
                                'en_cours_examen' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'recevable' => 'Recevable',
                                'irrecevable' => 'Irrecevable',
                                'en_cours_examen' => 'En cours d\'examen',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('date_decision_recevabilite')
                            ->label('Date de décision')
                            ->date('d/m/Y')
                            ->placeholder('Non décidée')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('motif_irrecevabilite')
                            ->label('Motif d\'irrecevabilité')
                            ->placeholder('N/A')
                            ->columnSpanFull()
                            ->visible(fn($record) => $record->statut_recevabilite === 'irrecevable'),
                    ])->columns(2),

                Infolists\Components\Section::make('Workflow de mise en état')
                    ->schema([
                        Infolists\Components\TextEntry::make('etape_actuelle')
                            ->label('Étape actuelle')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($state) => "Étape {$state}/11"),

                        Infolists\Components\TextEntry::make('statut_global')
                            ->label('Statut global')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'en_cours' => 'warning',
                                'cloture' => 'success',
                                'abandonne' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'en_cours' => 'En cours',
                                'cloture' => 'Clôturé',
                                'abandonne' => 'Abandonné',
                                default => $state,
                            }),

                        Infolists\Components\RepeatableEntry::make('etapes')
                            ->label('Détail des étapes')
                            ->schema([
                                Infolists\Components\TextEntry::make('numero_etape')
                                    ->label('N°')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('libelle')
                                    ->label('Étape')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('statut')
                                    ->label('Statut')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'completee' => 'success',
                                        'en_cours' => 'warning',
                                        'en_attente' => 'gray',
                                        'bloquee' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'completee' => 'Complétée',
                                        'en_cours' => 'En cours',
                                        'en_attente' => 'En attente',
                                        'bloquee' => 'Bloquée',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('date_debut')
                                    ->label('Début')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                Infolists\Components\TextEntry::make('date_completion')
                                    ->label('Complétée le')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                Infolists\Components\TextEntry::make('completePar.name')
                                    ->label('Par')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-user'),

                                Infolists\Components\TextEntry::make('observations')
                                    ->label('Observations')
                                    ->placeholder('Aucune')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6)
                            ->columnSpanFull(),
                    ])->collapsible(),

                Infolists\Components\Section::make('Observations')
                    ->schema([
                        Infolists\Components\TextEntry::make('observations')
                            ->label('Observations générales')
                            ->markdown()
                            ->placeholder('Aucune observation')
                            ->columnSpanFull(),
                    ])->collapsible(),

                Infolists\Components\Section::make('Gestion')
                    ->schema([
                        Infolists\Components\TextEntry::make('greffierResponsable.name')
                            ->label('Greffier responsable')
                            ->placeholder('Non assigné')
                            ->icon('heroicon-o-user-circle'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-o-clock'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-o-clock'),
                    ])->columns(3)
                    ->collapsible(),
            ]);
    }
}
