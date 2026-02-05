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
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Identification')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_rg')
                            ->label('Numéro RG')
                            ->copyable()
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('numero_parquet')
                            ->label('Numéro Parquet')
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('natureDecision.libelle')
                            ->label('Nature de décision')
                            ->badge(),

                        Infolists\Components\TextEntry::make('tribunal.nom')
                            ->label('Tribunal'),

                        Infolists\Components\TextEntry::make('tribunal.ville')
                            ->label('Ville')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'brouillon' => 'gray',
                                'en_attente_signature' => 'warning',
                                'signee' => 'info',
                                'enregistree' => 'success',
                                'archivee' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'brouillon' => 'Brouillon',
                                'en_attente_signature' => 'En attente signature',
                                'signee' => 'Signée',
                                'enregistree' => 'Enregistrée',
                                'archivee' => 'Archivée',
                                default => $state,
                            }),
                    ])->columns(3),

                Infolists\Components\Section::make('Dates importantes')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_decision')
                            ->label('Date de décision')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_factum')
                            ->label('Date du factum')
                            ->date('d/m/Y')
                            ->placeholder('Non renseignée')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_signature')
                            ->label('Date de signature')
                            ->date('d/m/Y')
                            ->placeholder('Non renseignée')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('date_enregistrement')
                            ->label('Date d\'enregistrement')
                            ->date('d/m/Y')
                            ->placeholder('Non renseignée')
                            ->icon('heroicon-o-calendar'),
                    ])->columns(4),

                Infolists\Components\Section::make('Composition du tribunal')
                    ->schema([
                        Infolists\Components\TextEntry::make('president')
                            ->label('Président')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('juge_1')
                            ->label('Juge 1')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('juge_2')
                            ->label('Juge 2')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('greffier')
                            ->label('Greffier')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('ministere_public')
                            ->label('Ministère Public')
                            ->placeholder('Non renseigné')
                            ->icon('heroicon-o-user'),
                    ])->columns(3),

                Infolists\Components\Section::make('Infractions')
                    ->schema([
                        Infolists\Components\TextEntry::make('infractions.libelle')
                            ->label('Infractions retenues')
                            ->badge()
                            ->separator(',')
                            ->placeholder('Aucune infraction'),
                    ]),

                Infolists\Components\Section::make('Parties au procès')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('parties')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'prevenu' => 'danger',
                                        'victime' => 'warning',
                                        'partie_civile' => 'info',
                                        'temoin' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'prevenu' => 'Prévenu',
                                        'victime' => 'Victime',
                                        'partie_civile' => 'Partie civile',
                                        'temoin' => 'Témoin',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Identité')
                                    ->getStateUsing(function ($record) {
                                        if ($record->is_personne_morale) {
                                            return $record->raison_sociale . ($record->representant_legal ? " (Rep: {$record->representant_legal})" : '');
                                        }
                                        return trim($record->nom . ' ' . $record->prenom);
                                    }),

                                Infolists\Components\TextEntry::make('date_naissance')
                                    ->label('Né(e) le')
                                    ->date('d/m/Y')
                                    ->placeholder('Non renseigné')
                                    ->visible(fn($record) => !$record->is_personne_morale),

                                Infolists\Components\TextEntry::make('profession')
                                    ->label('Profession')
                                    ->placeholder('Non renseignée')
                                    ->visible(fn($record) => !$record->is_personne_morale),

                                Infolists\Components\TextEntry::make('adresse')
                                    ->label('Adresse')
                                    ->placeholder('Non renseignée')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('telephone')
                                    ->label('Téléphone')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('Non renseigné'),

                                Infolists\Components\TextEntry::make('avocat_nom')
                                    ->label('Avocat')
                                    ->icon('heroicon-o-briefcase')
                                    ->placeholder('Aucun avocat'),
                            ])
                            ->columns(3)
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Détails de la décision')
                    ->schema([
                        Infolists\Components\TextEntry::make('resume')
                            ->label('Résumé des faits')
                            ->markdown()
                            ->placeholder('Non renseigné')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('dispositif')
                            ->label('Dispositif')
                            ->markdown()
                            ->placeholder('Non renseigné')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('montant_amende')
                            ->label('Montant de l\'amende')
                            ->money('XAF')
                            ->placeholder('Non applicable'),

                        Infolists\Components\TextEntry::make('duree_peine')
                            ->label('Durée de la peine')
                            ->placeholder('Non applicable'),
                    ])->columns(2),

                Infolists\Components\Section::make('Gestion')
                    ->schema([
                        Infolists\Components\TextEntry::make('greffierResponsable.name')
                            ->label('Greffier responsable')
                            ->placeholder('Non assigné')
                            ->icon('heroicon-o-user-circle'),

                        Infolists\Components\TextEntry::make('fichier_scan')
                            ->label('Fichier scanné')
                            ->placeholder('Aucun fichier')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null)
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-o-clock'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y à H:i')
                            ->icon('heroicon-o-clock'),
                    ])->columns(2),
            ]);
    }
}
