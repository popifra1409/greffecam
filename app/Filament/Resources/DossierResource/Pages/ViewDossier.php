<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewDossier extends ViewRecord
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clore')
                ->label('Clôturer le dossier')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn() => $this->record->peutEtreClos() && $this->record->statut !== 'clos')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'statut' => 'clos',
                        'date_cloture' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Dossier clôturé')
                        ->success()
                        ->send();
                }),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations du dossier')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_dossier')
                            ->label('Numéro de dossier')
                            ->copyable()
                            ->badge()
                            ->color('primary')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'ouvert' => 'success',
                                'en_instance' => 'warning',
                                'grosse_delivree' => 'info',
                                'en_recours' => 'danger',
                                'clos' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'ouvert' => 'Ouvert',
                                'en_instance' => 'En instance',
                                'grosse_delivree' => 'Grosse délivrée',
                                'en_recours' => 'En recours',
                                'clos' => 'Clos',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('tribunal.nom')
                            ->label('Tribunal')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('section.libelle')
                            ->label('Section')
                            ->badge(),

                        Infolists\Components\TextEntry::make('matiere.designation')
                            ->label('Matière')
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('anneeJudiciaire.libelle')
                            ->label('Année judiciaire')
                            ->badge(),

                        Infolists\Components\TextEntry::make('date_enrolement')
                            ->label('Date d\'enrôlement')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_cloture')
                            ->label('Date de clôture')
                            ->date('d/m/Y')
                            ->placeholder('Non clôturé')
                            ->icon('heroicon-o-calendar'),
                    ])->columns(3),

                Infolists\Components\Section::make('Demandeur')
                    ->schema([
                        Infolists\Components\TextEntry::make('demandeur_nom_complet')
                            ->label('Nom complet / Raison sociale')
                            ->getStateUsing(fn($record) => $record->demandeur_nom_complet)
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('demandeur_est_personne_morale')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state ? 'Personne morale' : 'Personne physique')
                            ->color(fn($state) => $state ? 'info' : 'success'),

                        Infolists\Components\TextEntry::make('demandeur_date_naissance')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('N/A')
                            ->visible(fn($record) => !$record->demandeur_est_personne_morale),

                        Infolists\Components\TextEntry::make('demandeur_lieu_naissance')
                            ->label('Lieu de naissance')
                            ->placeholder('N/A')
                            ->visible(fn($record) => !$record->demandeur_est_personne_morale),

                        Infolists\Components\TextEntry::make('demandeur_profession')
                            ->label('Profession')
                            ->placeholder('N/A')
                            ->visible(fn($record) => !$record->demandeur_est_personne_morale),

                        Infolists\Components\TextEntry::make('demandeur_nationalite')
                            ->label('Nationalité')
                            ->placeholder('N/A')
                            ->visible(fn($record) => !$record->demandeur_est_personne_morale),

                        Infolists\Components\TextEntry::make('demandeur_representant_legal')
                            ->label('Représentant légal')
                            ->placeholder('N/A')
                            ->visible(fn($record) => $record->demandeur_est_personne_morale),

                        Infolists\Components\TextEntry::make('demandeur_adresse')
                            ->label('Adresse')
                            ->placeholder('Non renseignée')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('demandeur_telephone')
                            ->label('Téléphone')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-phone'),

                        Infolists\Components\TextEntry::make('demandeur_email')
                            ->label('Email')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-envelope'),
                    ])->columns(3),

                Infolists\Components\Section::make('Avocat du demandeur')
                    ->schema([
                        Infolists\Components\TextEntry::make('avocat_demandeur_nom')
                            ->label('Nom de l\'avocat')
                            ->placeholder('Aucun avocat')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('avocat_demandeur_contact')
                            ->label('Contact')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-phone'),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Observations')
                    ->schema([
                        Infolists\Components\TextEntry::make('observations')
                            ->label('Observations')
                            ->markdown()
                            ->placeholder('Aucune observation')
                            ->columnSpanFull(),
                    ])->collapsible(),

                Infolists\Components\Section::make('Gestion')
                    ->schema([
                        Infolists\Components\TextEntry::make('enrolePar.name')
                            ->label('Enrôlé par')
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
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
