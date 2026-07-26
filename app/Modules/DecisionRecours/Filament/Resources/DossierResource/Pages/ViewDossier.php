<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DossierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DossierResource;
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
            Actions\Action::make('creer_decision')
                ->label('Créer une décision')
                ->icon('heroicon-o-scale')
                ->color('success')
                ->visible(fn($record) => in_array($record->statut, ['ouvert', 'en_instance']))
                ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('create', ['dossier_id' => $record->id])),

            // ✅ NOUVEAU : Créer un séquestre (nécessite qu'une décision existe déjà dans ce dossier)
            Actions\Action::make('creer_sequestre')
                ->label('Créer un séquestre')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn($record) => $record->decisions()->count() > 0)
                ->url(fn($record) => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl(
                    'create',
                    ['dossier_id' => $record->id],
                    panel: 'sequestre-caution'
                ))
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ✅ SECTION 1 : IDENTIFICATION DU DOSSIER
                Infolists\Components\Section::make('Identification du dossier')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_dossier')
                            ->label('Numéro de dossier')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('numero_dossier_personnalise')
                            ->label('Ancien numéro (personnalisé)')
                            ->badge()
                            ->color('gray')
                            ->placeholder('Non renseigné')
                            ->visible(fn($record) => $record->numero_dossier_personnalise),

                        Infolists\Components\TextEntry::make('tribunal.nom')
                            ->label('Tribunal')
                            ->icon('heroicon-o-building-office-2')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('section.libelle')
                            ->label('Section')
                            ->badge()
                            ->color(fn($record) => $record->section?->type === 'repressive' ? 'danger' : 'info'),

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

                        Infolists\Components\TextEntry::make('date_assignation')
                            ->label('Date d\'assignation')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar')
                            ->placeholder('Non renseignée'),

                        Infolists\Components\TextEntry::make('date_premiere_audience')
                            ->label('Date de première audience')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar-days')
                            ->badge()
                            ->color('info')
                            ->placeholder('Non renseignée'),

                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->size('lg')
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

                        Infolists\Components\TextEntry::make('enrolePar.name')
                            ->label('Enrôlé par')
                            ->icon('heroicon-o-user')
                            ->placeholder('Non renseigné'),
                    ])->columns(3),

                Infolists\Components\Section::make(function ($record) {
                    if ($record->section?->type === 'repressive') {
                        return 'Ministère Public et Parties Civiles';
                    }
                    return 'Demandeurs (Parties requérantes)';
                })
                    ->schema([
                        // ✅ CORRECTION : TextEntry au lieu de Placeholder
                        Infolists\Components\TextEntry::make('ministere_public_info')
                            ->label('')
                            ->html()
                            ->formatStateUsing(fn() => new \Illuminate\Support\HtmlString(
                                '<div style="padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0.5rem;">' .
                                    '<strong>⚖️ Ministère Public</strong><br>' .
                                    'Le Ministère Public est partie poursuivante d\'office.' .
                                    '</div>'
                            ))
                            ->visible(fn($record) => $record->section?->type === 'repressive')
                            ->columnSpanFull(),

                        // Toutes les parties requérantes
                        Infolists\Components\RepeatableEntry::make('demandeurs')
                            ->label(fn($record) => $record->section?->type === 'repressive' ? 'Parties Civiles' : 'Demandeurs')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Identité')
                                    ->getStateUsing(fn($record) => $record->nom_complet)
                                    ->weight('bold')
                                    ->size('lg')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('profession')
                                    ->label('Profession')
                                    ->visible(fn($record) => !$record->est_personne_morale && $record->profession)
                                    ->icon('heroicon-o-briefcase'),

                                Infolists\Components\TextEntry::make('nationalite')
                                    ->label('Nationalité')
                                    ->visible(fn($record) => !$record->est_personne_morale && $record->nationalite)
                                    ->badge(),

                                Infolists\Components\TextEntry::make('representant_legal')
                                    ->label('Représentant légal')
                                    ->visible(fn($record) => $record->est_personne_morale && $record->representant_legal),

                                Infolists\Components\TextEntry::make('adresse')
                                    ->label('Adresse')
                                    ->visible(fn($record) => $record->adresse)
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('telephone')
                                    ->label('Téléphone')
                                    ->icon('heroicon-o-phone')
                                    ->visible(fn($record) => $record->telephone),

                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->visible(fn($record) => $record->email),

                                Infolists\Components\TextEntry::make('avocat_nom')
                                    ->label('Avocat')
                                    ->icon('heroicon-o-scale')
                                    ->badge()
                                    ->color('gray')
                                    ->visible(fn($record) => $record->avocat_nom),

                                Infolists\Components\TextEntry::make('avocat_contact')
                                    ->label('Contact avocat')
                                    ->visible(fn($record) => $record->avocat_contact),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // ✅ SECTION 3 : PARTIES ADVERSES
                Infolists\Components\Section::make(function ($record) {
                    if ($record->section?->type === 'repressive') {
                        return 'Prévenus (Personnes poursuivies)';
                    }
                    return 'Défendeurs (Parties adverses)';
                })
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('defendeurs')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Identité')
                                    ->getStateUsing(fn($record) => $record->nom_complet)
                                    ->weight('bold')
                                    ->size('lg')
                                    ->badge()
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('profession')
                                    ->label('Profession')
                                    ->visible(fn($record) => !$record->est_personne_morale && $record->profession)
                                    ->icon('heroicon-o-briefcase'),

                                Infolists\Components\TextEntry::make('nationalite')
                                    ->label('Nationalité')
                                    ->visible(fn($record) => !$record->est_personne_morale && $record->nationalite)
                                    ->badge(),

                                Infolists\Components\TextEntry::make('representant_legal')
                                    ->label('Représentant légal')
                                    ->visible(fn($record) => $record->est_personne_morale && $record->representant_legal),

                                Infolists\Components\TextEntry::make('adresse')
                                    ->label('Adresse')
                                    ->visible(fn($record) => $record->adresse)
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('telephone')
                                    ->label('Téléphone')
                                    ->icon('heroicon-o-phone')
                                    ->visible(fn($record) => $record->telephone),

                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->visible(fn($record) => $record->email),

                                Infolists\Components\TextEntry::make('avocat_nom')
                                    ->label('Avocat')
                                    ->icon('heroicon-o-scale')
                                    ->badge()
                                    ->color('gray')
                                    ->visible(fn($record) => $record->avocat_nom),

                                Infolists\Components\TextEntry::make('avocat_contact')
                                    ->label('Contact avocat')
                                    ->visible(fn($record) => $record->avocat_contact),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // ✅ SECTION 4 : INFRACTIONS
                Infolists\Components\Section::make('Infractions / Objet du différend')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('infractions')
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

                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description')
                                    ->visible(fn($record) => $record->description)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record->infractions?->count() > 0)
                    ->collapsible(),

                // ✅ SECTION 5 : OBSERVATIONS
                Infolists\Components\Section::make('Observations')
                    ->schema([
                        Infolists\Components\TextEntry::make('observations')
                            ->label('')
                            ->markdown()
                            ->placeholder('Aucune observation')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record->observations)
                    ->collapsible()
                    ->collapsed(),

                // ✅ SECTION 6 : DÉCISIONS RENDUES
                Infolists\Components\Section::make('Décisions rendues')
                    ->schema([
                        Infolists\Components\TextEntry::make('decisions_count')
                            ->label('Nombre de décisions')
                            ->getStateUsing(fn($record) => $record->decisions()->count())
                            ->badge()
                            ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('decisions_info')
                            ->label('')
                            ->html()
                            ->formatStateUsing(function ($record) {
                                if ($record->decisions()->count() === 0) {
                                    return 'Aucune décision rendue pour ce dossier.';
                                }

                                return new \Illuminate\Support\HtmlString(
                                    'Consultez l\'onglet <strong>"Décisions rendues"</strong> ci-dessous pour voir le détail.'
                                );
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                // ✅ SECTION 6bis : SÉQUESTRES (Module Séquestre & Caution)
                Infolists\Components\Section::make('Séquestres')
                    ->schema([
                        Infolists\Components\TextEntry::make('sequestres_count')
                            ->label('Nombre de séquestres')
                            ->getStateUsing(fn($record) => $record->sequestres()->count())
                            ->badge()
                            ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('sequestres_solde_total')
                            ->label('Solde cumulé')
                            ->getStateUsing(function ($record) {
                                $total = $record->sequestres->sum(fn($s) => $s->solde_actuel);
                                return $total;
                            })
                            ->money('XAF')
                            ->weight('bold')
                            ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                            ->visible(fn($record) => $record->sequestres()->count() > 0),

                        Infolists\Components\TextEntry::make('sequestres_info')
                            ->label('')
                            ->html()
                            ->formatStateUsing(function ($record) {
                                if ($record->sequestres()->count() === 0) {
                                    return 'Aucun séquestre ouvert pour ce dossier.';
                                }

                                return new \Illuminate\Support\HtmlString(
                                    'Consultez l\'onglet <strong>"Séquestres"</strong> ci-dessous pour voir le détail.'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // ✅ SECTION 7 : MÉTADONNÉES
                Infolists\Components\Section::make('Métadonnées')
                    ->schema([
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
