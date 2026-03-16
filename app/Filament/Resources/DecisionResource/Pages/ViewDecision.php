<?php

namespace App\Filament\Resources\DecisionResource\Pages;

use App\Filament\Resources\DecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewDecision extends ViewRecord
{
    protected static string $resource = DecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn($record) => $record->estModifiable()),

            Actions\Action::make('transmettre')
                ->label('Transmettre')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn($record) => $record->peutEtreTransmise() && $record->detenteur_actuel_id === auth()->id())
                ->url(fn($record) => DecisionResource::getUrl('edit', ['record' => $record])),

            Actions\Action::make('signer')
                ->label('Signer')
                ->icon('heroicon-o-pencil-square')
                ->color('info')
                ->visible(fn($record) => $record->peutEtreSignee())
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'statut' => 'signee',
                        'date_signature' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Décision signée')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('enregistrer')
                ->label('Enregistrer')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn($record) => $record->peutEtreEnregistree())
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'statut' => 'enregistree',
                        'date_enregistrement' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Décision enregistrée')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->visible(fn($record) => $record->estModifiable()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ✅ SECTION 1 : INFORMATIONS DU DOSSIER
                Infolists\Components\Section::make('Dossier d\'enrôlement')
                    ->schema([
                        Infolists\Components\TextEntry::make('dossier.numero_dossier')
                            ->label('Numéro du dossier')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('dossier.tribunal.nom')
                            ->label('Tribunal')
                            ->icon('heroicon-o-building-office-2')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('dossier.section.libelle')
                            ->label('Section')
                            ->badge()
                            ->color(fn($record) => $record->dossier?->section?->type === 'repressive' ? 'danger' : 'info'),

                        Infolists\Components\TextEntry::make('dossier.matiere.designation')
                            ->label('Matière')
                            ->badge(),

                        Infolists\Components\TextEntry::make('dossier.anneeJudiciaire.libelle')
                            ->label('Année judiciaire')
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('dossier.date_enrolement')
                            ->label('Date d\'enrôlement')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                    ])->columns(3)
                    ->collapsible(),

                // ✅ SECTION 2 : PARTIES DU DOSSIER
                Infolists\Components\Section::make('Parties au litige')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                // DEMANDEUR / MINISTÈRE PUBLIC
                                Infolists\Components\Section::make(function ($record) {
                                    return $record->dossier?->section?->type === 'repressive'
                                        ? 'Ministère Public'
                                        : 'Demandeur';
                                })
                                    ->schema([
                                        Infolists\Components\TextEntry::make('dossier.demandeur_nom_complet')
                                            ->label('Identité')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->getStateUsing(fn($record) => $record->dossier?->demandeur_nom_complet),

                                        Infolists\Components\TextEntry::make('dossier.demandeur_profession')
                                            ->label('Profession')
                                            ->visible(fn($record) => $record->dossier && !$record->dossier->demandeur_est_personne_morale),

                                        Infolists\Components\TextEntry::make('dossier.demandeur_adresse')
                                            ->label('Adresse')
                                            ->visible(fn($record) => $record->dossier?->demandeur_adresse),

                                        Infolists\Components\TextEntry::make('dossier.demandeur_telephone')
                                            ->label('Téléphone')
                                            ->icon('heroicon-o-phone')
                                            ->visible(fn($record) => $record->dossier?->demandeur_telephone),

                                        Infolists\Components\TextEntry::make('dossier.avocat_demandeur_nom')
                                            ->label('Avocat')
                                            ->icon('heroicon-o-briefcase')
                                            ->badge()
                                            ->color('gray')
                                            ->visible(fn($record) => $record->dossier?->avocat_demandeur_nom),
                                    ])
                                    ->columnSpan(1),

                                // DÉFENDEUR / PRÉVENU
                                Infolists\Components\Section::make(function ($record) {
                                    return $record->dossier?->section?->type === 'repressive'
                                        ? 'Prévenu'
                                        : 'Défendeur';
                                })
                                    ->schema([
                                        Infolists\Components\TextEntry::make('dossier.defendeur_nom_complet')
                                            ->label('Identité')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->getStateUsing(fn($record) => $record->dossier?->defendeur_nom_complet),

                                        Infolists\Components\TextEntry::make('dossier.defendeur_profession')
                                            ->label('Profession')
                                            ->visible(fn($record) => $record->dossier && !$record->dossier->defendeur_est_personne_morale),

                                        Infolists\Components\TextEntry::make('dossier.defendeur_adresse')
                                            ->label('Adresse')
                                            ->visible(fn($record) => $record->dossier?->defendeur_adresse),

                                        Infolists\Components\TextEntry::make('dossier.defendeur_telephone')
                                            ->label('Téléphone')
                                            ->icon('heroicon-o-phone')
                                            ->visible(fn($record) => $record->dossier?->defendeur_telephone),

                                        Infolists\Components\TextEntry::make('dossier.avocat_defendeur_nom')
                                            ->label('Avocat')
                                            ->icon('heroicon-o-briefcase')
                                            ->badge()
                                            ->color('gray')
                                            ->visible(fn($record) => $record->dossier?->avocat_defendeur_nom),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),

                // ✅ SECTION 3 : INFRACTIONS
                Infolists\Components\Section::make('Infractions / Nature du différend')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('dossier.infractions')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('libelle')
                                    ->label('Infraction')
                                    ->badge()
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('code')
                                    ->label('Code')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('categorie')
                                    ->label('Catégorie')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'Crime' => 'danger',
                                        'Délit' => 'warning',
                                        'Contravention' => 'info',
                                        default => 'gray',
                                    }),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record->dossier?->infractions?->count() > 0)
                    ->collapsible()
                    ->collapsed(),

                // ✅ SECTION 4 : IDENTIFICATION DE LA DÉCISION
                Infolists\Components\Section::make('Identification de la décision')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_rg')
                            ->label('Numéro RG')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('numero_repertoire')
                            ->label('N° Répertoire / Décision')
                            ->badge()
                            ->copyable()
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('numero_parquet')
                            ->label('Numéro Parquet')
                            ->badge()
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('natureDecision.libelle')
                            ->label('Nature de la décision')
                            ->badge()
                            ->color('info')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->size('lg')
                            ->color(fn(string $state): string => match ($state) {
                                'brouillon' => 'gray',
                                'transmise_chef' => 'warning',
                                'signee' => 'info',
                                'enregistree' => 'success',
                                'annulee' => 'danger',
                                'archivee' => 'secondary',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'brouillon' => 'Brouillon',
                                'transmise_chef' => 'Transmise au chef',
                                'signee' => 'Signée',
                                'enregistree' => 'Enregistrée',
                                'annulee' => 'Annulée',
                                'archivee' => 'Archivée',
                                default => $state,
                            }),
                    ])->columns(3),

                // ✅ SECTION 5 : DATES
                Infolists\Components\Section::make('Dates importantes')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_decision')
                            ->label('Date de décision')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('date_saisie')
                            ->label('Date de saisie')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock'),

                        Infolists\Components\TextEntry::make('date_factum')
                            ->label('Date du factum')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-document-text')
                            ->placeholder('Non renseignée'),

                        Infolists\Components\TextEntry::make('date_signature')
                            ->label('Date de signature')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-pencil-square')
                            ->placeholder('Non signée'),

                        Infolists\Components\TextEntry::make('date_enregistrement')
                            ->label('Date d\'enregistrement')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-check-badge')
                            ->placeholder('Non enregistrée'),
                    ])->columns(3)
                    ->collapsible(),

                // ✅ SECTION 6 : COMPOSITION DU TRIBUNAL
                Infolists\Components\Section::make('Composition du Tribunal')
                    ->schema([
                        Infolists\Components\TextEntry::make('mode_composition')
                            ->label('Mode de composition')
                            ->badge()
                            ->size('lg')
                            ->color(fn($state) => $state === 'juge_unique' ? 'info' : 'warning')
                            ->formatStateUsing(fn($state) => $state === 'juge_unique' ? 'Juge unique' : 'Collège de juges (Collégialité)')
                            ->columnSpanFull(),

                        // SI JUGE UNIQUE
                        Infolists\Components\TextEntry::make('jugeUnique.nom_complet')
                            ->label('Juge')
                            ->icon('heroicon-o-user')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->visible(fn($record) => $record->mode_composition === 'juge_unique')
                            ->placeholder('Non renseigné'),

                        // SI COLLÈGE
                        Infolists\Components\TextEntry::make('collegeJuge.designation')
                            ->label('Collège de juges')
                            ->icon('heroicon-o-user-group')
                            ->badge()
                            ->color('warning')
                            ->size('lg')
                            ->visible(fn($record) => $record->mode_composition === 'college')
                            ->placeholder('Non renseigné'),

                        // MEMBRES DU COLLÈGE (si collège sélectionné)
                        Infolists\Components\RepeatableEntry::make('collegeJuge.membres')
                            ->label('Membres du collège')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Juge')
                                    ->getStateUsing(fn($record) => $record->nom_complet)
                                    ->badge(),

                                Infolists\Components\TextEntry::make('pivot.qualite')
                                    ->label('Qualité')
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'president' => 'Président',
                                        'juge_1' => 'Juge 1',
                                        'juge_2' => 'Juge 2',
                                        'assesseur_1' => 'Assesseur 1',
                                        'assesseur_2' => 'Assesseur 2',
                                        'juge_suppleant' => 'Juge suppléant',
                                        default => $state,
                                    }),
                            ])
                            ->columns(2)
                            ->visible(fn($record) => $record->mode_composition === 'college' && $record->collegeJuge)
                            ->columnSpanFull(),

                        // GREFFIER
                        Infolists\Components\TextEntry::make('greffierDecision.nom_complet')
                            ->label('Greffier')
                            ->icon('heroicon-o-identification')
                            ->badge()
                            ->color('gray')
                            ->placeholder('Non renseigné'),
                    ])->columns(2),

                // ✅ SECTION 7 : CONTENU DE LA DÉCISION
                Infolists\Components\Section::make('Contenu de la décision')
                    ->schema([
                        Infolists\Components\TextEntry::make('resume')
                            ->label('Résumé des faits')
                            ->markdown()
                            ->columnSpanFull()
                            ->placeholder('Aucun résumé'),

                        Infolists\Components\TextEntry::make('dispositif')
                            ->label('Dispositif')
                            ->markdown()
                            ->columnSpanFull()
                            ->placeholder('Aucun dispositif'),
                    ])
                    ->collapsible(),

                // ✅ SECTION 8 : CONDAMNATIONS
                Infolists\Components\Section::make('Condamnations')
                    ->schema([
                        Infolists\Components\TextEntry::make('montant_amende')
                            ->label('Amende')
                            ->money('XAF')
                            ->placeholder('Aucune amende'),

                        Infolists\Components\TextEntry::make('montant_depens')
                            ->label('Dépens')
                            ->money('XAF')
                            ->placeholder('Aucun dépens'),

                        Infolists\Components\TextEntry::make('duree_peine')
                            ->label('Durée de la peine')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Aucune peine privative de liberté'),
                    ])->columns(3)
                    ->visible(fn($record) => $record->montant_amende || $record->montant_depens || $record->duree_peine)
                    ->collapsible()
                    ->collapsed(),

                // ✅ SECTION 9 : GESTION
                Infolists\Components\Section::make('Gestion')
                    ->schema([
                        Infolists\Components\TextEntry::make('greffierResponsable.name')
                            ->label('Greffier responsable du dossier')
                            ->icon('heroicon-o-user-circle')
                            ->badge()
                            ->placeholder('Non assigné'),

                        Infolists\Components\TextEntry::make('fichier_scan')
                            ->label('Fichier scanné')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->placeholder('Aucun fichier'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y à H:i'),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
