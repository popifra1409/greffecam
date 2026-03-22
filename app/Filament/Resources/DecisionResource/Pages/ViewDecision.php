<?php

namespace App\Filament\Resources\DecisionResource\Pages;

use App\Filament\Resources\DecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Forms;

class ViewDecision extends ViewRecord
{
    protected static string $resource = DecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ✅ ACTION PDF : APERÇU
            Actions\Action::make('apercu_pdf')
                ->label('Aperçu PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn($record) => route('decisions.etat.apercu', ['decision' => $record]))
                ->openUrlInNewTab(),

            // ✅ ACTION PDF : TÉLÉCHARGER
            Actions\Action::make('telecharger_pdf')
                ->label('Télécharger PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn($record) => route('decisions.etat.telecharger', ['decision' => $record]))
                ->openUrlInNewTab(),

            Actions\EditAction::make()
                ->visible(fn($record) => $record->estModifiable()),

            // ✅ ACTION 1 : VALIDER (brouillon → validee)
            Actions\Action::make('valider')
                ->label('Valider la décision')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn($record) => $record->peutEtreValidee())
                ->requiresConfirmation()
                ->modalHeading('Valider cette décision')
                ->modalDescription('La décision passera au statut "Validée" et pourra être saisie.')
                ->modalSubmitActionLabel('Valider')
                ->action(function ($record) {
                    $record->update(['statut' => 'validee']);

                    \Filament\Notifications\Notification::make()
                        ->title('✅ Décision validée')
                        ->body('La décision peut maintenant être saisie.')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 2 : SAISIR (validee → saisie)
            Actions\Action::make('saisir')
                ->label('Marquer comme saisie')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->visible(fn($record) => $record->peutEtreSaisie())
                ->form([
                    Forms\Components\DatePicker::make('date_saisie')
                        ->label('Date de saisie')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()),

                    Forms\Components\FileUpload::make('fichier_saisi')
                        ->label('Fichier saisi (Word, etc.)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/msword',
                            'application/pdf'
                        ])
                        ->maxSize(10240)
                        ->directory('decisions/saisies')
                        ->required()
                        ->helperText('Uploadez le fichier de la décision saisie'),
                ])
                ->modalHeading('Marquer la décision comme saisie')
                ->modalSubmitActionLabel('Enregistrer')
                ->action(function ($record, array $data) {
                    $record->update([
                        'statut' => 'saisie',
                        'date_saisie' => $data['date_saisie'],
                        'fichier_saisi' => $data['fichier_saisi'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('⌨️ Décision saisie')
                        ->body('La décision peut maintenant être signée.')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 3 : MODIFIER LE FICHIER SAISI (optionnel, statut saisie)
            Actions\Action::make('modifier_fichier_saisi')
                ->label('Modifier le fichier saisi')
                ->icon('heroicon-o-pencil-square')
                ->color('info')
                ->visible(fn($record) => $record->statut === 'saisie')
                ->form([
                    Forms\Components\DatePicker::make('date_modification')
                        ->label('Date de modification')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()),

                    Forms\Components\FileUpload::make('fichier_saisi_modifie')
                        ->label('Fichier saisi modifié')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/msword',
                            'application/pdf'
                        ])
                        ->maxSize(10240)
                        ->directory('decisions/saisies')
                        ->required()
                        ->helperText('Uploadez la nouvelle version du fichier'),
                ])
                ->modalHeading('Modifier le fichier saisi')
                ->modalSubmitActionLabel('Enregistrer la modification')
                ->action(function ($record, array $data) {
                    $record->update([
                        'date_modification' => $data['date_modification'],
                        'fichier_saisi_modifie' => $data['fichier_saisi_modifie'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('🔄 Fichier modifié')
                        ->body('Le fichier saisi a été mis à jour.')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 4 : SIGNER (saisie → signee)
            Actions\Action::make('signer')
                ->label('Marquer comme signée')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->visible(fn($record) => $record->peutEtreSignee())
                ->form([
                    Forms\Components\DatePicker::make('date_signature')
                        ->label('Date de signature')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()),

                    Forms\Components\FileUpload::make('fichier_signe')
                        ->label('Fichier signé (PDF)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->directory('decisions/signees')
                        ->required()
                        ->helperText('Uploadez le fichier PDF de la décision signée'),
                ])
                ->modalHeading('Marquer la décision comme signée')
                ->modalSubmitActionLabel('Enregistrer')
                ->action(function ($record, array $data) {
                    $record->update([
                        'statut' => 'signee',
                        'date_signature' => $data['date_signature'],
                        'fichier_signe' => $data['fichier_signe'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('✍️ Décision signée')
                        ->body('La décision peut maintenant être enregistrée.')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 5 : ENREGISTRER (signee → enregistree)
            Actions\Action::make('enregistrer')
                ->label('Marquer comme enregistrée')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn($record) => $record->peutEtreEnregistree())
                ->form([
                    Forms\Components\Section::make('Date et fichier')
                        ->schema([
                            Forms\Components\DatePicker::make('date_enregistrement')
                                ->label('Date d\'enregistrement')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(now()),

                            Forms\Components\FileUpload::make('fichier_enregistre')
                                ->label('Fichier enregistré (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->directory('decisions/enregistrees')
                                ->required()
                                ->helperText('Uploadez le fichier PDF de la décision enregistrée')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make('Références d\'enregistrement')
                        ->description('Tous les champs sont obligatoires')
                        ->schema([
                            Forms\Components\TextInput::make('numero_volume')
                                ->label('N° Volume')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('numero_folio')
                                ->label('N° Folio')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('numero_case_bd')
                                ->label('N° Case BD')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('numero_quittance')
                                ->label('N° Quittance')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('montant_quittance')
                                ->label('Montant de la quittance')
                                ->numeric()
                                ->suffix('FCFA')
                                ->required(),
                        ])->columns(3),
                ])
                ->modalHeading('Enregistrer la décision')
                ->modalSubmitActionLabel('Enregistrer')
                ->modalWidth('3xl')
                ->action(function ($record, array $data) {
                    $record->update([
                        'statut' => 'enregistree',
                        'date_enregistrement' => $data['date_enregistrement'],
                        'fichier_enregistre' => $data['fichier_enregistre'],
                        'numero_volume' => $data['numero_volume'],
                        'numero_folio' => $data['numero_folio'],
                        'numero_case_bd' => $data['numero_case_bd'],
                        'numero_quittance' => $data['numero_quittance'],
                        'montant_quittance' => $data['montant_quittance'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('📋 Décision enregistrée')
                        ->body('La décision est enregistrée. Vous pouvez maintenant ajouter le certificat/grosse ou déclarer une opposition.')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 6 : CERTIFICAT & GROSSE (statut enregistree, sans opposition)
            Actions\Action::make('certificat_grosse')
                ->label('Certificat & Grosse')
                ->icon('heroicon-o-document-check')
                ->color('info')
                ->visible(fn($record) => $record->statut === 'enregistree' && !$record->a_opposition)
                ->form([
                    Forms\Components\Section::make('Certificat de non-appel')
                        ->schema([
                            Forms\Components\TextInput::make('certificat_non_appel_reference')
                                ->label('Référence')
                                ->maxLength(255),

                            Forms\Components\DatePicker::make('certificat_non_appel_date')
                                ->label('Date')
                                ->native(false)
                                ->displayFormat('d/m/Y'),

                            Forms\Components\FileUpload::make('certificat_non_appel_fichier')
                                ->label('Fichier (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->directory('decisions/certificats')
                                ->columnSpanFull(),
                        ])->columns(2)
                        ->collapsible(),

                    Forms\Components\Section::make('Grosse')
                        ->schema([
                            Forms\Components\TextInput::make('grosse_reference')
                                ->label('Référence')
                                ->maxLength(255),

                            Forms\Components\DatePicker::make('grosse_date')
                                ->label('Date')
                                ->native(false)
                                ->displayFormat('d/m/Y'),

                            Forms\Components\FileUpload::make('grosse_fichier')
                                ->label('Fichier (PDF)')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->directory('decisions/grosses')
                                ->columnSpanFull(),
                        ])->columns(2)
                        ->collapsible(),
                ])
                ->modalHeading('Ajouter le certificat de non-appel et la grosse')
                ->modalSubmitActionLabel('Enregistrer')
                ->modalWidth('3xl')
                ->action(function ($record, array $data) {
                    $record->update($data);

                    \Filament\Notifications\Notification::make()
                        ->title('✅ Certificat et grosse enregistrés')
                        ->body('Les documents ont été ajoutés à la décision.')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 7 : DÉCLARER OPPOSITION (statut enregistree)
            Actions\Action::make('declarer_opposition')
                ->label('Déclarer une opposition')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->visible(fn($record) => $record->statut === 'enregistree' && !$record->a_opposition)
                ->form([
                    Forms\Components\TextInput::make('lettre_opposition_reference')
                        ->label('Référence de la lettre d\'opposition')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('lettre_opposition_date')
                        ->label('Date de la lettre')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()),

                    Forms\Components\FileUpload::make('lettre_opposition_fichier')
                        ->label('Fichier de la lettre (PDF)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->directory('decisions/oppositions')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('info')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString(
                            '<div style="padding: 1rem; background: #fee2e2; border-left: 4px solid #dc2626; border-radius: 0.5rem;">' .
                                '<strong>⚠️ Attention</strong><br>' .
                                'En déclarant une opposition, le module Recours sera activé pour traiter cette affaire.' .
                                '</div>'
                        ))
                        ->columnSpanFull(),
                ])
                ->modalHeading('Déclarer une opposition')
                ->modalSubmitActionLabel('Enregistrer l\'opposition')
                ->modalWidth('2xl')
                ->action(function ($record, array $data) {
                    $record->update([
                        'a_opposition' => true,
                        'lettre_opposition_reference' => $data['lettre_opposition_reference'],
                        'lettre_opposition_date' => $data['lettre_opposition_date'],
                        'lettre_opposition_fichier' => $data['lettre_opposition_fichier'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('⚠️ Opposition enregistrée')
                        ->body('Le module Recours doit être activé pour traiter cette opposition.')
                        ->warning()
                        ->send();
                }),

            // ✅ ACTION 8 : ARCHIVER (enregistree → archivee)
            Actions\Action::make('archiver')
                ->label('Archiver')
                ->icon('heroicon-o-archive-box')
                ->color('secondary')
                ->visible(fn($record) => $record->peutEtreArchivee())
                ->requiresConfirmation()
                ->modalHeading('Archiver cette décision')
                ->modalDescription('Une fois archivée, la décision ne pourra plus être modifiée.')
                ->modalSubmitActionLabel('Archiver')
                ->action(function ($record) {
                    $record->update([
                        'statut' => 'archivee',
                        'is_archived' => true,
                        'date_archivage' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('📦 Décision archivée')
                        ->body('La décision a été archivée avec succès.')
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
                // ✅ SECTION 1 : DOSSIER
                Infolists\Components\Section::make('Dossier d\'enrôlement')
                    ->schema([
                        Infolists\Components\TextEntry::make('dossier.numero_dossier')
                            ->label('Numéro du dossier')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold')
                            ->url(fn($record) => $record->dossier
                                ? \App\Filament\Resources\DossierResource::getUrl('view', ['record' => $record->dossier])
                                : null)
                            ->icon('heroicon-o-arrow-top-right-on-square'),

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
                    ])->columns(3)
                    ->collapsible(),

                // ✅ SECTION 2 : PARTIES
                Infolists\Components\Section::make(function ($record) {
                    if ($record->dossier?->section?->type === 'repressive') {
                        return 'Ministère Public et Parties Civiles';
                    }
                    return 'Demandeurs (Parties requérantes)';
                })
                    ->schema([
                        Infolists\Components\TextEntry::make('ministere_public_info')
                            ->label('')
                            ->html()
                            ->formatStateUsing(fn() => new \Illuminate\Support\HtmlString(
                                '<div style="padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0.5rem;">' .
                                    '<strong>⚖️ Ministère Public</strong><br>' .
                                    'Le Ministère Public est partie poursuivante d\'office.' .
                                    '</div>'
                            ))
                            ->visible(fn($record) => $record->dossier?->section?->type === 'repressive')
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('dossier.demandeurs')
                            ->label(fn($record) => $record->dossier?->section?->type === 'repressive' ? 'Parties Civiles' : 'Demandeurs')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Identité')
                                    ->getStateUsing(fn($record) => $record->nom_complet)
                                    ->weight('bold')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('profession')
                                    ->label('Profession')
                                    ->visible(fn($record) => !$record->est_personne_morale && $record->profession),

                                Infolists\Components\TextEntry::make('avocat_nom')
                                    ->label('Avocat')
                                    ->badge()
                                    ->color('gray')
                                    ->visible(fn($record) => $record->avocat_nom),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make(function ($record) {
                    if ($record->dossier?->section?->type === 'repressive') {
                        return 'Prévenus';
                    }
                    return 'Défendeurs (Parties adverses)';
                })
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('dossier.defendeurs')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->label('Identité')
                                    ->getStateUsing(fn($record) => $record->nom_complet)
                                    ->weight('bold')
                                    ->badge()
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('profession')
                                    ->label('Profession')
                                    ->visible(fn($record) => !$record->est_personne_morale && $record->profession),

                                Infolists\Components\TextEntry::make('avocat_nom')
                                    ->label('Avocat')
                                    ->badge()
                                    ->color('gray')
                                    ->visible(fn($record) => $record->avocat_nom),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // ✅ SECTION 3 : IDENTIFICATION
                Infolists\Components\Section::make('Identification de la décision')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_repertoire')
                            ->label('N° Répertoire / Décision')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold')
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
                                'validee' => 'info',
                                'saisie' => 'warning',
                                'signee' => 'primary',
                                'enregistree' => 'success',
                                'archivee' => 'secondary',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'brouillon' => 'Brouillon',
                                'validee' => 'Validée',
                                'saisie' => 'Saisie',
                                'signee' => 'Signée',
                                'enregistree' => 'Enregistrée',
                                'archivee' => 'Archivée',
                                default => $state,
                            }),
                    ])->columns(2),

                // ✅ SECTION 4 : DATES WORKFLOW
                Infolists\Components\Section::make('Dates du workflow')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_decision')
                            ->label('📅 Date de décision')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('date_factum')
                            ->label('📄 Date du factum')
                            ->date('d/m/Y')
                            ->placeholder('Non renseignée'),

                        Infolists\Components\TextEntry::make('date_premiere_audience')
                            ->label('⚖️ Date de 1ère audience')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('info')
                            ->placeholder('Non renseignée'),

                        Infolists\Components\TextEntry::make('date_saisie')
                            ->label('⌨️ Date de saisie')
                            ->date('d/m/Y')
                            ->placeholder('Non saisie')
                            ->visible(fn($record) => in_array($record->statut, ['saisie', 'signee', 'enregistree', 'archivee'])),

                        Infolists\Components\TextEntry::make('date_modification')
                            ->label('🔄 Date de modification')
                            ->date('d/m/Y')
                            ->placeholder('Pas de modification')
                            ->visible(fn($record) => $record->date_modification),

                        Infolists\Components\TextEntry::make('date_signature')
                            ->label('✍️ Date de signature')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('primary')
                            ->placeholder('Non signée')
                            ->visible(fn($record) => in_array($record->statut, ['signee', 'enregistree', 'archivee'])),

                        Infolists\Components\TextEntry::make('date_enregistrement')
                            ->label('📋 Date d\'enregistrement')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('success')
                            ->placeholder('Non enregistrée')
                            ->visible(fn($record) => in_array($record->statut, ['enregistree', 'archivee'])),
                    ])->columns(3)
                    ->collapsible(),

                // ✅ SECTION 5 : COMPOSITION
                Infolists\Components\Section::make('Composition du Tribunal')
                    ->schema([
                        Infolists\Components\TextEntry::make('mode_composition')
                            ->label('Mode de composition')
                            ->badge()
                            ->size('lg')
                            ->color(fn($state) => $state === 'juge_unique' ? 'info' : 'warning')
                            ->formatStateUsing(fn($state) => $state === 'juge_unique' ? 'Juge unique' : 'Collège de juges')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('jugeUnique.nom_complet')
                            ->label('Juge')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->visible(fn($record) => $record->mode_composition === 'juge_unique')
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('collegeJuge.designation')
                            ->label('Collège de juges')
                            ->badge()
                            ->color('warning')
                            ->size('lg')
                            ->visible(fn($record) => $record->mode_composition === 'college')
                            ->placeholder('Non renseigné'),

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

                        Infolists\Components\TextEntry::make('greffierDecision.nom_complet')
                            ->label('Greffier')
                            ->badge()
                            ->color('gray')
                            ->placeholder('Non renseigné'),
                    ])->columns(2)
                    ->collapsible(),

                // ✅ SECTION 6 : DÉCISION
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

                // ✅ SECTION 7 : CONDAMNATIONS
                Infolists\Components\Section::make('Condamnations')
                    ->schema([
                        Infolists\Components\TextEntry::make('montant_amende')
                            ->label('Amende')
                            ->money('XAF')
                            ->placeholder('Aucune'),

                        Infolists\Components\TextEntry::make('montant_depens')
                            ->label('Dépens')
                            ->money('XAF')
                            ->placeholder('Aucun'),

                        Infolists\Components\TextEntry::make('duree_peine')
                            ->label('Peine privative de liberté')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Aucune'),
                    ])->columns(3)
                    ->visible(fn($record) => $record->montant_amende || $record->montant_depens || $record->duree_peine)
                    ->collapsible()
                    ->collapsed(),

                // ✅ SECTION 8 : FICHIERS
                Infolists\Components\Section::make('Fichiers numériques')
                    ->schema([
                        Infolists\Components\TextEntry::make('fichier_saisi')
                            ->label('📄 Fichier saisi')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->color('warning')
                            ->placeholder('Pas de fichier')
                            ->visible(fn($record) => in_array($record->statut, ['saisie', 'signee', 'enregistree', 'archivee'])),

                        Infolists\Components\TextEntry::make('fichier_saisi_modifie')
                            ->label('📝 Fichier saisi modifié')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->color('info')
                            ->placeholder('Pas de modification')
                            ->visible(fn($record) => $record->fichier_saisi_modifie),

                        Infolists\Components\TextEntry::make('fichier_signe')
                            ->label('✍️ Fichier signé')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->color('primary')
                            ->placeholder('Pas de fichier')
                            ->visible(fn($record) => in_array($record->statut, ['signee', 'enregistree', 'archivee'])),

                        Infolists\Components\TextEntry::make('fichier_enregistre')
                            ->label('📋 Fichier enregistré')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->color('success')
                            ->placeholder('Pas de fichier')
                            ->visible(fn($record) => in_array($record->statut, ['enregistree', 'archivee'])),

                        Infolists\Components\TextEntry::make('fichier_scan')
                            ->label('📎 Autre fichier scanné')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->placeholder('Pas de fichier')
                            ->visible(fn($record) => $record->fichier_scan),
                    ])->columns(2)
                    ->collapsible(),

                // ✅ SECTION 9 : ENREGISTREMENT
                Infolists\Components\Section::make('Références d\'enregistrement')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_volume')
                            ->label('N° Volume')
                            ->badge(),

                        Infolists\Components\TextEntry::make('numero_folio')
                            ->label('N° Folio')
                            ->badge(),

                        Infolists\Components\TextEntry::make('numero_case_bd')
                            ->label('N° Case BD')
                            ->badge(),

                        Infolists\Components\TextEntry::make('numero_quittance')
                            ->label('N° Quittance')
                            ->badge(),

                        Infolists\Components\TextEntry::make('montant_quittance')
                            ->label('Montant quittance')
                            ->money('XAF'),
                    ])->columns(3)
                    ->visible(fn($record) => in_array($record->statut, ['enregistree', 'archivee']))
                    ->collapsible(),

                // ✅ SECTION 10 : CERTIFICAT & GROSSE (si pas d'opposition)
                Infolists\Components\Section::make('Certificat de non-appel & Grosse')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\Section::make('Certificat de non-appel')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('certificat_non_appel_reference')
                                            ->label('Référence')
                                            ->badge()
                                            ->placeholder('Non renseignée'),

                                        Infolists\Components\TextEntry::make('certificat_non_appel_date')
                                            ->label('Date')
                                            ->date('d/m/Y')
                                            ->placeholder('Non renseignée'),

                                        Infolists\Components\TextEntry::make('certificat_non_appel_fichier')
                                            ->label('Fichier')
                                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                                            ->openUrlInNewTab()
                                            ->badge()
                                            ->placeholder('Pas de fichier'),
                                    ])
                                    ->columnSpan(1),

                                Infolists\Components\Section::make('Grosse')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('grosse_reference')
                                            ->label('Référence')
                                            ->badge()
                                            ->placeholder('Non renseignée'),

                                        Infolists\Components\TextEntry::make('grosse_date')
                                            ->label('Date')
                                            ->date('d/m/Y')
                                            ->placeholder('Non renseignée'),

                                        Infolists\Components\TextEntry::make('grosse_fichier')
                                            ->label('Fichier')
                                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                                            ->openUrlInNewTab()
                                            ->badge()
                                            ->placeholder('Pas de fichier'),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->visible(fn($record) => !$record->a_opposition && in_array($record->statut, ['enregistree', 'archivee']))
                    ->collapsible()
                    ->collapsed(),

                // ✅ SECTION 11 : OPPOSITION
                Infolists\Components\Section::make('Opposition')
                    ->schema([
                        Infolists\Components\TextEntry::make('opposition_info')
                            ->label('')
                            ->html()
                            ->formatStateUsing(fn() => new \Illuminate\Support\HtmlString(
                                '<div style="padding: 1rem; background: #fee2e2; border-left: 4px solid #dc2626; border-radius: 0.5rem;">' .
                                    '<strong>⚠️ Opposition enregistrée</strong><br>' .
                                    'Cette décision a fait l\'objet d\'une opposition. Le module Recours doit être activé.' .
                                    '</div>'
                            ))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('lettre_opposition_reference')
                            ->label('Référence de la lettre')
                            ->badge()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('lettre_opposition_date')
                            ->label('Date')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('lettre_opposition_fichier')
                            ->label('Fichier')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->color('danger')
                            ->placeholder('Pas de fichier'),
                    ])->columns(3)
                    ->visible(fn($record) => $record->a_opposition)
                    ->collapsible(),

                // ✅ SECTION 12 : MÉTADONNÉES
                Infolists\Components\Section::make('Gestion')
                    ->schema([
                        Infolists\Components\TextEntry::make('greffierResponsable.name')
                            ->label('Greffier responsable')
                            ->badge()
                            ->placeholder('Non assigné'),

                        Infolists\Components\IconEntry::make('is_archived')
                            ->label('Archivée')
                            ->boolean()
                            ->trueIcon('heroicon-o-archive-box')
                            ->falseIcon('heroicon-o-folder-open')
                            ->trueColor('secondary')
                            ->falseColor('success'),

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
