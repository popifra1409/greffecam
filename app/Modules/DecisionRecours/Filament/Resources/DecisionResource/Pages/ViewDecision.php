<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DecisionResource;
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

            // ACTION 1 : VALIDER
            Actions\Action::make('valider')
                ->label('Valider la décision')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn($record) => $record->peutEtreValidee())
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update(['statut' => 'validee']);
                    \Filament\Notifications\Notification::make()
                        ->title('✅ Décision validée')
                        ->success()
                        ->send();
                }),

            // ACTION 2 : SAISIR
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
                        ->default(now()),
                    Forms\Components\FileUpload::make('fichier_saisi')
                        ->label('Fichier saisi')
                        ->required()
                        ->directory('decisions/saisies'),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'statut' => 'saisie',
                        'date_saisie' => $data['date_saisie'],
                        'fichier_saisi' => $data['fichier_saisi'],
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->title('⌨️ Décision saisie')
                        ->success()
                        ->send();
                }),

            // ACTION 3 : MODIFIER FICHIER SAISI
            Actions\Action::make('modifier_fichier_saisi')
                ->label('Modifier le fichier saisi')
                ->icon('heroicon-o-pencil-square')
                ->color('info')
                ->visible(fn($record) => $record->statut === 'saisie')
                ->form([
                    Forms\Components\DatePicker::make('date_modification')
                        ->required()
                        ->native(false)
                        ->default(now()),
                    Forms\Components\FileUpload::make('fichier_saisi_modifie')
                        ->required()
                        ->directory('decisions/saisies'),
                ])
                ->action(function ($record, array $data) {
                    $record->update($data);
                    \Filament\Notifications\Notification::make()
                        ->title('🔄 Fichier modifié')
                        ->success()
                        ->send();
                }),

            // ACTION 4 : SIGNER
            Actions\Action::make('signer')
                ->label('Marquer comme signée')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->visible(fn($record) => $record->peutEtreSignee())
                ->form([
                    Forms\Components\DatePicker::make('date_signature')
                        ->required()
                        ->native(false)
                        ->default(now()),
                    Forms\Components\FileUpload::make('fichier_signe')
                        ->required()
                        ->directory('decisions/signees'),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'statut' => 'signee',
                        'date_signature' => $data['date_signature'],
                        'fichier_signe' => $data['fichier_signe'],
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->title('✍️ Décision signée')
                        ->success()
                        ->send();
                }),

            // ACTION 5 : ENREGISTRER
            Actions\Action::make('enregistrer')
                ->label('Marquer comme enregistrée')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn($record) => $record->peutEtreEnregistree())
                ->form([
                    Forms\Components\Section::make('Date et fichier')
                        ->schema([
                            Forms\Components\DatePicker::make('date_enregistrement')
                                ->required()
                                ->native(false)
                                ->default(now()),
                            Forms\Components\FileUpload::make('fichier_enregistre')
                                ->required()
                                ->directory('decisions/enregistrees')
                                ->columnSpanFull(),
                        ])->columns(2),
                    Forms\Components\Section::make('Références')
                        ->schema([
                            Forms\Components\TextInput::make('numero_volume')->required(),
                            Forms\Components\TextInput::make('numero_folio')->required(),
                            Forms\Components\TextInput::make('numero_case_bd')->required(),
                            Forms\Components\TextInput::make('numero_quittance')->required(),
                            Forms\Components\TextInput::make('montant_quittance')->numeric()->required(),
                        ])->columns(3),
                ])
                ->modalWidth('3xl')
                ->action(function ($record, array $data) {
                    $record->update(array_merge(['statut' => 'enregistree'], $data));
                    \Filament\Notifications\Notification::make()
                        ->title('📋 Décision enregistrée')
                        ->success()
                        ->send();
                }),

            // ACTION 6 : CERTIFICAT & GROSSE
            Actions\Action::make('certificat_grosse')
                ->label('Certificat & Grosse')
                ->icon('heroicon-o-document-check')
                ->color('info')
                ->visible(fn($record) => $record->statut === 'enregistree' && !$record->type_recours)
                ->form([
                    Forms\Components\Section::make('Certificat de non-appel')
                        ->schema([
                            Forms\Components\TextInput::make('certificat_non_appel_reference'),
                            Forms\Components\DatePicker::make('certificat_non_appel_date')->native(false),
                            Forms\Components\FileUpload::make('certificat_non_appel_fichier')
                                ->directory('decisions/certificats')
                                ->columnSpanFull(),
                        ])->columns(2)->collapsible(),
                    Forms\Components\Section::make('Grosse')
                        ->schema([
                            Forms\Components\TextInput::make('grosse_reference'),
                            Forms\Components\DatePicker::make('grosse_date')->native(false),
                            Forms\Components\FileUpload::make('grosse_fichier')
                                ->directory('decisions/grosses')
                                ->columnSpanFull(),
                        ])->columns(2)->collapsible(),
                ])
                ->modalWidth('3xl')
                ->action(function ($record, array $data) {
                    $record->update($data);
                    \Filament\Notifications\Notification::make()
                        ->title('✅ Certificat et grosse enregistrés')
                        ->success()
                        ->send();
                }),

            // ACTION 7 : ARCHIVER
            Actions\Action::make('archiver')
                ->label('Archiver')
                ->icon('heroicon-o-archive-box')
                ->color('secondary')
                ->visible(fn($record) => $record->peutEtreArchivee())
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'statut' => 'archivee',
                        'is_archived' => true,
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->title('📦 Décision archivée')
                        ->success()
                        ->send();
                }),

            // ✅ ACTION 8 : DÉCLARATION DE VOIE DE RECOURS (APRÈS ARCHIVER)
            Actions\Action::make('declarer_recours')
                ->label('Déclaration de voie de recours')
                ->icon('heroicon-o-scale')
                ->color('danger')
                ->visible(
                    fn($record) =>
                    in_array($record->statut, ['saisie', 'signee', 'enregistree', 'archivee']) &&
                    !$record->type_recours
                )
                ->form([
                    Forms\Components\Select::make('type_recours_id')
                        ->label('Type de recours')
                        ->required()
                        ->relationship('typeRecours', 'libelle', function ($query) {
                            return $query->where('is_active', true);
                        })
                        ->getOptionLabelFromRecordUsing(
                            fn($record) =>
                            ($record->icone ?? '⚖️') . ' ' . $record->libelle
                        )
                        ->searchable()
                        ->preload()
                        ->live(),

                    Forms\Components\DatePicker::make('date_recours')
                        ->label('Date du recours')
                        ->required()
                        ->native(false)
                        ->default(now()),

                    Forms\Components\TextInput::make('reference_lettre')
                        ->label('Référence de la lettre')
                        ->required(),

                    Forms\Components\FileUpload::make('fichier_lettre')
                        ->label('Lettre de déclaration (PDF)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory('decisions/recours')
                        ->required(),
                ])
                ->modalHeading('Déclaration de voie de recours')
                ->modalWidth('2xl')
                ->action(function ($record, array $data) {
                    // Récupérer le TypeRecours
                    $typeRecours = \App\Models\TypeRecours::find($data['type_recours_id']);

                    // Mettre à jour la décision
                    $record->update([
                        'type_recours' => $typeRecours->code, // Pour compatibilité
                    ]);

                    // Créer le recours
                    $recours = \App\Models\Recours::create([
                        'decision_id' => $record->id,
                        'type_recours_id' => $data['type_recours_id'],
                        'type_recours' => $typeRecours->code,
                        'date_recours' => $data['date_recours'],
                        'reference_lettre' => $data['reference_lettre'],
                        'fichier_lettre' => $data['fichier_lettre'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('⚖️ Recours enregistré')
                        ->body('Le recours N°' . $recours->id . ' a été créé.')
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('voir')
                                ->label('Voir le recours')
                                ->url(\App\Modules\DecisionRecours\Filament\Resources\RecoursResource::getUrl('edit', ['record' => $recours]))
                                ->button(),
                        ])
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
                                ? \App\Modules\DecisionRecours\Filament\Resources\DossierResource::getUrl('view', ['record' => $record->dossier])
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

                // ✅ SECTION 2 : IDENTIFICATION (NOUVELLE HIÉRARCHIE)
                Infolists\Components\Section::make('Classification de la décision')
                    ->schema([
                        Infolists\Components\TextEntry::make('categorieDecision.libelle')
                            ->label('Catégorie')
                            ->badge()
                            ->color('gray')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('typeDecision.libelle')
                            ->label('Type')
                            ->badge()
                            ->color('info')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('natureDecision.libelle')
                            ->label('Nature')
                            ->badge()
                            ->color('warning')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('numero_repertoire')
                            ->label('N° Répertoire')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable()
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('numero_parquet')
                            ->label('N° Parquet')
                            ->badge()
                            ->placeholder('Non renseigné'),

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
                    ])->columns(3),

                // ✅ SECTION 3 : DATES
                Infolists\Components\Section::make('Dates du workflow')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_decision')
                            ->label('📅 Date de décision (Prononcé)')
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

                // ✅ NOUVELLE SECTION : SIGNIFICATION
                Infolists\Components\Section::make('Signification')
                    ->schema([
                        Infolists\Components\IconEntry::make('est_signifiee')
                            ->label('Décision signifiée')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('date_signification')
                            ->label('Date de signification')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('warning')
                            ->placeholder('Non signifiée')
                            ->visible(fn($record) => $record->est_signifiee),

                        Infolists\Components\TextEntry::make('reference_acte_huissier')
                            ->label('Référence acte huissier')
                            ->badge()
                            ->placeholder('Non renseignée')
                            ->visible(fn($record) => $record->est_signifiee),

                        Infolists\Components\TextEntry::make('fichier_signification')
                            ->label('📎 Acte de signification')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->placeholder('Pas de fichier')
                            ->visible(fn($record) => $record->fichier_signification),
                    ])->columns(2)
                    ->visible(fn($record) => $record->est_signifiee)
                    ->collapsible(),

                // ✅ NOUVELLE SECTION : RECOURS
                Infolists\Components\Section::make('Voie de recours')
                    ->schema([
                        Infolists\Components\TextEntry::make('type_recours')
                            ->label('Type de recours')
                            ->badge()
                            ->size('lg')
                            ->color(fn(?string $state): string => match ($state) {
                                'appel' => 'danger',
                                'opposition' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'appel' => '⚖️ APPEL (Décision contradictoire)',
                                'opposition' => '⚠️ OPPOSITION (Décision par défaut)',
                                default => 'Aucun recours',
                            })
                            ->columnSpanFull(),

                        // APPEL
                        Infolists\Components\TextEntry::make('lettre_appel_reference')
                            ->label('Référence lettre d\'appel')
                            ->badge()
                            ->color('danger')
                            ->visible(fn($record) => $record->type_recours === 'appel'),

                        Infolists\Components\TextEntry::make('lettre_appel_date')
                            ->label('Date de déclaration')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('danger')
                            ->visible(fn($record) => $record->type_recours === 'appel'),

                        Infolists\Components\TextEntry::make('lettre_appel_fichier')
                            ->label('📎 Lettre d\'appel')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->visible(fn($record) => $record->lettre_appel_fichier),

                        // OPPOSITION
                        Infolists\Components\TextEntry::make('lettre_opposition_reference')
                            ->label('Référence lettre d\'opposition')
                            ->badge()
                            ->color('warning')
                            ->visible(fn($record) => $record->type_recours === 'opposition'),

                        Infolists\Components\TextEntry::make('lettre_opposition_date')
                            ->label('Date de dépôt')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('warning')
                            ->visible(fn($record) => $record->type_recours === 'opposition'),

                        Infolists\Components\TextEntry::make('lettre_opposition_fichier')
                            ->label('📎 Lettre d\'opposition')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->visible(fn($record) => $record->lettre_opposition_fichier),
                    ])->columns(3)
                    ->visible(fn($record) => $record->type_recours)
                    ->collapsible(),

                // ✅ COMPOSITION (inchangée)
                Infolists\Components\Section::make('Composition du Tribunal')
                    ->schema([
                        Infolists\Components\TextEntry::make('mode_composition')
                            ->label('Mode de composition')
                            ->badge()
                            ->size('lg')
                            ->color(fn($state) => $state === 'juge_unique' ? 'info' : 'warning')
                            ->formatStateUsing(fn($state) => $state === 'juge_unique' ? 'Juge unique' : 'Collège de juges'),

                        Infolists\Components\TextEntry::make('jugeUnique.nom_complet')
                            ->label('Juge')
                            ->badge()
                            ->color('primary')
                            ->visible(fn($record) => $record->mode_composition === 'juge_unique')
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('collegeJuge.designation')
                            ->label('Collège')
                            ->badge()
                            ->color('warning')
                            ->visible(fn($record) => $record->mode_composition === 'college')
                            ->placeholder('Non renseigné'),

                        Infolists\Components\TextEntry::make('greffierDecision.nom_complet')
                            ->label('Greffier')
                            ->badge()
                            ->color('gray')
                            ->placeholder('Non renseigné'),
                    ])->columns(2)
                    ->collapsible(),

                // ✅ DÉCISION (inchangée)
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

                // ✅ CERTIFICAT & GROSSE (si pas de recours)
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
                                            ->label('📎 Fichier')
                                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                                            ->openUrlInNewTab()
                                            ->badge()
                                            ->placeholder('Pas de fichier'),
                                    ]),

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
                                            ->label('📎 Fichier')
                                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                                            ->openUrlInNewTab()
                                            ->badge()
                                            ->placeholder('Pas de fichier'),
                                    ]),
                            ]),
                    ])
                    ->visible(fn($record) => !$record->type_recours && in_array($record->statut, ['enregistree', 'archivee']))
                    ->collapsible()
                    ->collapsed(),

                // ✅ FICHIERS (inchangée)
                Infolists\Components\Section::make('Fichiers numériques')
                    ->schema([
                        Infolists\Components\TextEntry::make('fichier_saisi')
                            ->label('📄 Fichier saisi')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->placeholder('Pas de fichier')
                            ->visible(fn($record) => in_array($record->statut, ['saisie', 'signee', 'enregistree', 'archivee'])),

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
                    ])->columns(3)
                    ->collapsible(),

                // ✅ ENREGISTREMENT (inchangée)
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
                            ->label('Montant')
                            ->money('XAF'),
                    ])->columns(3)
                    ->visible(fn($record) => in_array($record->statut, ['enregistree', 'archivee']))
                    ->collapsible(),
            ]);
    }
}