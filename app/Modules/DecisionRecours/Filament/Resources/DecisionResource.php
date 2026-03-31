<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\DecisionResource\Pages;
use App\Models\Decision;
use App\Models\Dossier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;

class DecisionResource extends Resource
{
    protected static ?string $model = Decision::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Décision';

    protected static ?string $pluralModelLabel = 'Décisions';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        // ✅ ONGLET 1 : SÉLECTION DU DOSSIER
                        Forms\Components\Tabs\Tab::make('Dossier')
                            ->schema([
                                Forms\Components\Select::make('dossier_id')
                                    ->label('Dossier d\'enrôlement')
                                    ->relationship('dossier', 'numero_dossier', function ($query) {
                                        return $query->whereIn('statut', ['ouvert', 'en_instance'])
                                            ->with(['tribunal', 'section', 'matiere']);
                                    })
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) =>
                                        "{$record->numero_dossier} - {$record->demandeurs_liste} vs {$record->defendeurs_liste}"
                                    )
                                    ->searchable(['numero_dossier'])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $dossier = Dossier::with(['tribunal', 'section', 'matiere', 'anneeJudiciaire'])->find($state);
                                            if ($dossier) {
                                                $set('tribunal_id', $dossier->tribunal_id);
                                                $set('section_id', $dossier->section_id);
                                                $set('matiere_id', $dossier->matiere_id);
                                                $set('annee_judiciaire_id', $dossier->annee_judiciaire_id);
                                            }
                                        }
                                    })
                                    ->helperText('Sélectionnez le dossier pour lequel vous rendez cette décision')
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('Informations héritées du dossier')
                                    ->schema([
                                        Forms\Components\Placeholder::make('dossier_info')
                                            ->label('')
                                            ->content(function (Get $get) {
                                                $dossierId = $get('dossier_id');
                                                if (!$dossierId) {
                                                    return 'Sélectionnez un dossier pour voir ses informations';
                                                }

                                                $dossier = Dossier::with([
                                                    'tribunal',
                                                    'section',
                                                    'matiere',
                                                    'infractions'
                                                ])->find($dossierId);

                                                if (!$dossier) return '';

                                                return new \Illuminate\Support\HtmlString(
                                                    '<div style="font-family: monospace; line-height: 2;">' .
                                                        '<strong>Tribunal :</strong> ' . ($dossier->tribunal?->nom ?? 'N/A') . '<br>' .
                                                        '<strong>Section :</strong> ' . ($dossier->section?->libelle ?? 'N/A') . '<br>' .
                                                        '<strong>Matière :</strong> ' . ($dossier->matiere?->designation ?? 'N/A') . '<br>' .
                                                        '<strong>Requérants :</strong> ' . ($dossier->demandeurs_liste ?: $dossier->demandeur_nom_complet) . '<br>' .
                                                        '<strong>Parties adverses :</strong> ' . ($dossier->defendeurs_liste ?: $dossier->defendeur_nom_complet) . '<br>' .
                                                        '<strong>Infractions :</strong> ' . $dossier->infractions->pluck('libelle')->join(', ') . '<br>' .
                                                        '<strong>Date enrôlement :</strong> ' . $dossier->date_enrolement?->format('d/m/Y') .
                                                        '</div>'
                                                );
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn(Get $get) => $get('dossier_id'))
                                    ->collapsible(),

                                Forms\Components\Hidden::make('tribunal_id'),
                                Forms\Components\Hidden::make('section_id'),
                                Forms\Components\Hidden::make('matiere_id'),
                                Forms\Components\Hidden::make('annee_judiciaire_id'),
                            ]),

                        // ✅ ONGLET 2 : IDENTIFICATION
                        Forms\Components\Tabs\Tab::make('Identification')
                            ->schema([
                                Forms\Components\Section::make('Classification')
                                    ->schema([
                                        // ✅ NOUVELLE HIÉRARCHIE : Catégorie > Type > Nature
                                        Forms\Components\Select::make('categorie_decision_id')
                                            ->label('Catégorie de décision')
                                            ->relationship('categorieDecision', 'libelle')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn(callable $set) => $set('type_decision_id', null)),

                                        Forms\Components\Select::make('type_decision_id')
                                            ->label('Type de décision')
                                            ->relationship(
                                                'typeDecision',
                                                'libelle',
                                                fn(Builder $query, Get $get) => $query->when(
                                                    $get('categorie_decision_id'),
                                                    fn($q, $catId) => $q->where('categorie_decision_id', $catId)
                                                )
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled(fn(Get $get) => !$get('categorie_decision_id'))
                                            ->helperText('Sélectionnez d\'abord une catégorie'),

                                        Forms\Components\Select::make('nature_decision_id')
                                            ->label('Nature de décision')
                                            ->relationship('natureDecision', 'libelle')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->helperText('Contradictoire, Par défaut, etc.'),
                                    ])->columns(3),

                                Forms\Components\Section::make('Numéros')
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_repertoire')
                                            ->label('N° Répertoire / N° Décision')
                                            ->maxLength(255)
                                            ->placeholder('Numéro de la décision'),

                                        Forms\Components\TextInput::make('numero_parquet')
                                            ->label('Numéro Parquet')
                                            ->maxLength(255)
                                            ->placeholder('Référence du parquet'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Dates')
                                    ->description('Ordre : Décision → Factum → Saisie → Signature → Enregistrement')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_decision')
                                            ->label('Date de décision (Prononcé)')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->default(now())
                                            ->helperText('Date du prononcé de la décision'),

                                        Forms\Components\DatePicker::make('date_factum')
                                            ->label('Date du factum')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\DatePicker::make('date_premiere_audience')
                                            ->label('Date de première audience')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->helperText('Date prévue pour la première audience'),

                                        // ✅ Les dates suivantes selon le statut
                                        Forms\Components\DatePicker::make('date_saisie')
                                            ->label('Date de saisie')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn(Get $get) => in_array($get('statut'), ['saisie', 'signee', 'enregistree', 'archivee']))
                                            ->required(fn(Get $get) => $get('statut') === 'saisie'),

                                        Forms\Components\DatePicker::make('date_modification')
                                            ->label('Date de modification')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn(Get $get) => in_array($get('statut'), ['saisie', 'signee', 'enregistree', 'archivee']))
                                            ->helperText('Si le fichier saisi a été modifié'),

                                        Forms\Components\DatePicker::make('date_signature')
                                            ->label('Date de signature')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn(Get $get) => in_array($get('statut'), ['signee', 'enregistree', 'archivee']))
                                            ->required(fn(Get $get) => $get('statut') === 'signee'),

                                        Forms\Components\DatePicker::make('date_enregistrement')
                                            ->label('Date d\'enregistrement')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn(Get $get) => in_array($get('statut'), ['enregistree', 'archivee']))
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),
                                    ])->columns(3),

                                Forms\Components\Section::make('Statut')
                                    ->schema([
                                        Forms\Components\Select::make('statut')
                                            ->label('Statut')
                                            ->options([
                                                'brouillon' => 'Brouillon',
                                                'validee' => 'Validée',
                                                'saisie' => 'Saisie',
                                                'signee' => 'Signée',
                                                'enregistree' => 'Enregistrée',
                                                'archivee' => 'Archivée',
                                            ])
                                            ->default('brouillon')
                                            ->required()
                                            ->live()
                                            ->disabled(fn($record) => $record && $record->statut !== 'brouillon'),
                                    ])->columns(1),
                            ]),

                        // ✅ ONGLET 3 : COMPOSITION DU TRIBUNAL
                        Forms\Components\Tabs\Tab::make('Composition du Tribunal')
                            ->schema([
                                Forms\Components\Radio::make('mode_composition')
                                    ->label('Mode de composition')
                                    ->options([
                                        'juge_unique' => 'Juge unique',
                                        'college' => 'Collège de juges (Collégialité)',
                                    ])
                                    ->default('juge_unique')
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('Juge unique')
                                    ->schema([
                                        Forms\Components\Select::make('juge_unique_id')
                                            ->label('Juge')
                                            ->relationship('jugeUnique', 'nom', function ($query, Get $get) {
                                                $tribunalId = $get('tribunal_id');
                                                if ($tribunalId) {
                                                    return $query->where('tribunal_id', $tribunalId)
                                                        ->where('is_active', true);
                                                }
                                                return $query->where('is_active', true);
                                            })
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->nom_complet)
                                            ->searchable(['nom', 'prenom'])
                                            ->preload()
                                            ->required(fn(Get $get) => $get('mode_composition') === 'juge_unique'),
                                    ])
                                    ->visible(fn(Get $get) => $get('mode_composition') === 'juge_unique'),

                                Forms\Components\Section::make('Collège de juges')
                                    ->schema([
                                        Forms\Components\Select::make('college_juge_id')
                                            ->label('Collège')
                                            ->relationship('collegeJuge', 'designation', function ($query, Get $get) {
                                                $tribunalId = $get('tribunal_id');
                                                if ($tribunalId) {
                                                    return $query->where('tribunal_id', $tribunalId)
                                                        ->where('is_active', true);
                                                }
                                                return $query->where('is_active', true);
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required(fn(Get $get) => $get('mode_composition') === 'college')
                                            ->helperText('Sélectionnez le collège de juges qui a rendu la décision'),
                                    ])
                                    ->visible(fn(Get $get) => $get('mode_composition') === 'college'),

                                Forms\Components\Section::make('Greffier')
                                    ->schema([
                                        Forms\Components\Select::make('greffier_id')
                                            ->label('Greffier')
                                            ->relationship('greffierDecision', 'nom', function ($query, Get $get) {
                                                $tribunalId = $get('tribunal_id');
                                                if ($tribunalId) {
                                                    return $query->where('tribunal_id', $tribunalId)
                                                        ->where('is_active', true);
                                                }
                                                return $query->where('is_active', true);
                                            })
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->nom_complet)
                                            ->searchable(['nom', 'prenom'])
                                            ->preload()
                                            ->helperText('Greffier ayant assisté à l\'audience'),
                                    ]),
                            ]),

                        // ✅ ONGLET 4 : DÉCISION
                        Forms\Components\Tabs\Tab::make('Décision')
                            ->schema([
                                Forms\Components\Section::make('Faits et dispositif')
                                    ->schema([
                                        Forms\Components\Textarea::make('resume')
                                            ->label('Résumé des faits')
                                            ->maxLength(65535)
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('dispositif')
                                            ->label('Dispositif')
                                            ->required()
                                            ->maxLength(65535)
                                            ->rows(5)
                                            ->columnSpanFull()
                                            ->helperText('Décision du tribunal'),
                                    ]),

                                Forms\Components\Section::make('Condamnation pécuniaire')
                                    ->schema([
                                        Forms\Components\TextInput::make('montant_amende')
                                            ->label('Montant de l\'amende')
                                            ->numeric()
                                            ->suffix('FCFA')
                                            ->placeholder('0'),

                                        Forms\Components\TextInput::make('montant_depens')
                                            ->label('Montant des dépens')
                                            ->numeric()
                                            ->suffix('FCFA')
                                            ->placeholder('0')
                                            ->helperText('Frais et dépens de justice'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->collapsed(),

                                Forms\Components\Section::make('Peine privative de liberté')
                                    ->schema([
                                        Forms\Components\TextInput::make('duree_peine')
                                            ->label('Durée de la peine')
                                            ->maxLength(255)
                                            ->placeholder('Ex: 2 ans, 6 mois'),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ]),

                        // ✅ ONGLET 5 : FICHIERS (Selon statut)
                        Forms\Components\Tabs\Tab::make('Fichiers')
                            ->schema([
                                // FICHIER SAISI
                                Forms\Components\Section::make('Saisie')
                                    ->schema([
                                        Forms\Components\FileUpload::make('fichier_saisi')
                                            ->label('Fichier saisi (Word, etc.)')
                                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/saisies')
                                            ->required(fn(Get $get) => $get('statut') === 'saisie'),

                                        Forms\Components\FileUpload::make('fichier_saisi_modifie')
                                            ->label('Fichier saisi modifié')
                                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/saisies')
                                            ->helperText('Si modification après première saisie'),
                                    ])
                                    ->visible(fn(Get $get) => in_array($get('statut'), ['saisie', 'signee', 'enregistree', 'archivee']))
                                    ->columns(2)
                                    ->collapsible(),

                                // FICHIER SIGNÉ
                                Forms\Components\Section::make('Signature')
                                    ->schema([
                                        Forms\Components\FileUpload::make('fichier_signe')
                                            ->label('Fichier signé (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/signees')
                                            ->required(fn(Get $get) => $get('statut') === 'signee'),
                                    ])
                                    ->visible(fn(Get $get) => in_array($get('statut'), ['signee', 'enregistree', 'archivee']))
                                    ->collapsible(),

                                // FICHIER ENREGISTRÉ
                                Forms\Components\Section::make('Enregistrement')
                                    ->schema([
                                        Forms\Components\FileUpload::make('fichier_enregistre')
                                            ->label('Fichier enregistré (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/enregistrees')
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),
                                    ])
                                    ->visible(fn(Get $get) => in_array($get('statut'), ['enregistree', 'archivee']))
                                    ->collapsible(),

                                Forms\Components\FileUpload::make('fichier_scan')
                                    ->label('Autre fichier scanné')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->directory('decisions/scans')
                                    ->columnSpanFull(),
                            ]),

                        // ✅ ONGLET 6 : ENREGISTREMENT
                        Forms\Components\Tabs\Tab::make('Enregistrement')
                            ->schema([
                                Forms\Components\Section::make('Références d\'enregistrement')
                                    ->description('À remplir lors du statut "Enregistrée"')
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_volume')
                                            ->label('N° Volume')
                                            ->maxLength(255)
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),

                                        Forms\Components\TextInput::make('numero_folio')
                                            ->label('N° Folio')
                                            ->maxLength(255)
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),

                                        Forms\Components\TextInput::make('numero_case_bd')
                                            ->label('N° Case BD')
                                            ->maxLength(255)
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),

                                        Forms\Components\TextInput::make('numero_quittance')
                                            ->label('N° Quittance')
                                            ->maxLength(255)
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),

                                        Forms\Components\TextInput::make('montant_quittance')
                                            ->label('Montant de la quittance')
                                            ->numeric()
                                            ->suffix('FCFA')
                                            ->required(fn(Get $get) => $get('statut') === 'enregistree'),
                                    ])
                                    ->columns(3)
                                    ->visible(fn(Get $get) => in_array($get('statut'), ['enregistree', 'archivee'])),
                            ]),

                        // ✅ ONGLET 7 : SIGNIFICATION & RECOURS (NOUVEAU WORKFLOW)
                        Forms\Components\Tabs\Tab::make('Signification & Recours')
                            ->schema([
                                // ✅ SECTION SIGNIFICATION
                                Forms\Components\Section::make('Signification')
                                    ->description('Remise de la copie de la décision à la partie adverse par acte d\'huissier')
                                    ->schema([
                                        Forms\Components\Toggle::make('est_signifiee')
                                            ->label('Cette décision a été signifiée')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\DatePicker::make('date_signification')
                                            ->label('Date de signification')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->required(fn(Get $get) => $get('est_signifiee'))
                                            ->visible(fn(Get $get) => $get('est_signifiee')),

                                        Forms\Components\TextInput::make('reference_acte_huissier')
                                            ->label('Référence acte d\'huissier')
                                            ->maxLength(255)
                                            ->visible(fn(Get $get) => $get('est_signifiee')),

                                        Forms\Components\FileUpload::make('fichier_signification')
                                            ->label('Acte de signification (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/significations')
                                            ->visible(fn(Get $get) => $get('est_signifiee'))
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                // ✅ SECTION TYPE DE RECOURS
                                Forms\Components\Section::make('Voie de recours')
                                    ->description('⚖️ Appel (décision contradictoire) ou Opposition (décision par défaut)')
                                    ->schema([
                                        Forms\Components\Select::make('type_recours')
                                            ->label('Type de recours')
                                            ->options([
                                                'appel' => 'Appel (Décision contradictoire)',
                                                'opposition' => 'Opposition (Décision par défaut)',
                                            ])
                                            ->live()
                                            ->helperText(function (Get $get) {
                                                $type = $get('type_recours');
                                                if ($type === 'appel') {
                                                    return '✅ APPEL : Peut être déclaré DÈS LE PRONONCÉ (même avant saisie/signature)';
                                                } elseif ($type === 'opposition') {
                                                    return '⚠️ OPPOSITION : Uniquement APRÈS SIGNIFICATION de la décision';
                                                }
                                                return 'Sélectionnez le type de recours si applicable';
                                            })
                                            ->columnSpanFull(),

                                        // ✅ VÉRIFICATION : Opposition requiert signification
                                        Forms\Components\Placeholder::make('warning_opposition')
                                            ->label('')
                                            ->content(new \Illuminate\Support\HtmlString(
                                                '<div style="padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0.5rem;">' .
                                                    '<strong>⚠️ Attention</strong><br>' .
                                                    'Une opposition ne peut être faite que si la décision a été signifiée. Assurez-vous de remplir la section Signification ci-dessus.' .
                                                    '</div>'
                                            ))
                                            ->visible(fn(Get $get) => $get('type_recours') === 'opposition' && !$get('est_signifiee'))
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),

                                // ✅ APPEL (Décision contradictoire)
                                Forms\Components\Section::make('Déclaration d\'appel')
                                    ->description('Lettre de déclaration d\'appel')
                                    ->schema([
                                        Forms\Components\TextInput::make('lettre_appel_reference')
                                            ->label('Référence de la lettre d\'appel')
                                            ->maxLength(255)
                                            ->required(fn(Get $get) => $get('type_recours') === 'appel'),

                                        Forms\Components\DatePicker::make('lettre_appel_date')
                                            ->label('Date de déclaration d\'appel')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->required(fn(Get $get) => $get('type_recours') === 'appel')
                                            ->after('date_decision')
                                            ->helperText('Date de dépôt de la déclaration d\'appel'),

                                        Forms\Components\FileUpload::make('lettre_appel_fichier')
                                            ->label('Lettre de déclaration d\'appel (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/appels')
                                            ->required(fn(Get $get) => $get('type_recours') === 'appel')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn(Get $get) => $get('type_recours') === 'appel')
                                    ->collapsible(),

                                // ✅ OPPOSITION (Décision par défaut)
                                Forms\Components\Section::make('Lettre d\'opposition')
                                    ->description('Lettre d\'opposition à la décision')
                                    ->schema([
                                        Forms\Components\TextInput::make('lettre_opposition_reference')
                                            ->label('Référence de la lettre d\'opposition')
                                            ->maxLength(255)
                                            ->required(fn(Get $get) => $get('type_recours') === 'opposition'),

                                        Forms\Components\DatePicker::make('lettre_opposition_date')
                                            ->label('Date de dépôt de l\'opposition')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->required(fn(Get $get) => $get('type_recours') === 'opposition')
                                            ->after('date_signification')
                                            ->helperText('Date de dépôt de l\'opposition'),

                                        Forms\Components\FileUpload::make('lettre_opposition_fichier')
                                            ->label('Lettre d\'opposition (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/oppositions')
                                            ->required(fn(Get $get) => $get('type_recours') === 'opposition')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn(Get $get) => $get('type_recours') === 'opposition')
                                    ->collapsible(),

                                // ✅ INFO RECOURS DÉCLENCHÉ
                                Forms\Components\Placeholder::make('info_recours_active')
                                    ->label('')
                                    ->content(fn(Get $get) => new \Illuminate\Support\HtmlString(
                                        '<div style="padding: 1rem; background: #fee2e2; border-left: 4px solid #dc2626; border-radius: 0.5rem;">' .
                                            '<strong>⚖️ Recours activé : ' . ($get('type_recours') === 'appel' ? 'APPEL' : 'OPPOSITION') . '</strong><br>' .
                                            'Le module Recours sera automatiquement activé pour traiter ce ' . ($get('type_recours') === 'appel' ? 'recours en appel' : 'recours en opposition') . '.' .
                                            '</div>'
                                    ))
                                    ->visible(fn(Get $get) => in_array($get('type_recours'), ['appel', 'opposition']))
                                    ->columnSpanFull(),

                                // ✅ CERTIFICAT & GROSSE (Si pas de recours)
                                Forms\Components\Section::make('Certificat de non-appel & Grosse')
                                    ->description('À remplir uniquement s\'il n\'y a PAS de recours')
                                    ->schema([
                                        Forms\Components\TextInput::make('certificat_non_appel_reference')
                                            ->label('Référence du certificat de non-appel')
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('certificat_non_appel_date')
                                            ->label('Date du certificat')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\FileUpload::make('certificat_non_appel_fichier')
                                            ->label('Certificat de non-appel (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/certificats')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('grosse_reference')
                                            ->label('Référence de la grosse')
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('grosse_date')
                                            ->label('Date de la grosse')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\FileUpload::make('grosse_fichier')
                                            ->label('Fichier de la grosse (PDF)')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240)
                                            ->directory('decisions/grosses')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn(Get $get) => !$get('type_recours'))
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->visible(fn(Get $get) => in_array($get('statut'), ['signee', 'enregistree', 'archivee'])),

                        // ✅ ONGLET 8 : GESTION
                        Forms\Components\Tabs\Tab::make('Gestion')
                            ->schema([
                                Forms\Components\Select::make('greffier_responsable_id')
                                    ->label('Greffier responsable du dossier')
                                    ->relationship('greffierResponsable', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Greffier chargé du suivi de la décision'),

                                Forms\Components\Toggle::make('is_archived')
                                    ->label('Archiver cette décision')
                                    ->helperText('Une fois archivée, la décision ne peut plus être modifiée')
                                    ->visible(fn(Get $get) => $get('statut') === 'enregistree'),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    // ✅ TABLE (mise à jour pour afficher type_recours)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->label('N° Dossier')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('numero_repertoire')
                    ->label('N° Décision')
                    ->searchable()
                    ->badge()
                    ->copyable(),

                Tables\Columns\TextColumn::make('date_decision')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('categorieDecision.libelle')
                    ->label('Catégorie')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('typeDecision.libelle')
                    ->label('Type')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->wrap(),

                Tables\Columns\TextColumn::make('natureDecision.libelle')
                    ->label('Nature')
                    ->searchable()
                    ->badge()
                    ->wrap(),

                Tables\Columns\TextColumn::make('composition')
                    ->label('Composition')
                    ->getStateUsing(fn($record) => $record->composition)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
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
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('est_signifiee')
                    ->label('Signifiée')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type_recours')
                    ->label('Recours')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'appel' => 'danger',
                        'opposition' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'appel' => 'APPEL',
                        'opposition' => 'OPPOSITION',
                        default => 'Aucun',
                    })
                    ->placeholder('Aucun')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'validee' => 'Validée',
                        'saisie' => 'Saisie',
                        'signee' => 'Signée',
                        'enregistree' => 'Enregistrée',
                        'archivee' => 'Archivée',
                    ]),

                Tables\Filters\SelectFilter::make('type_recours')
                    ->label('Type de recours')
                    ->options([
                        'appel' => 'Appel',
                        'opposition' => 'Opposition',
                    ]),

                Tables\Filters\TernaryFilter::make('est_signifiee')
                    ->label('Signification')
                    ->placeholder('Tous')
                    ->trueLabel('Signifiée')
                    ->falseLabel('Non signifiée'),

                Tables\Filters\SelectFilter::make('categorie_decision_id')
                    ->label('Catégorie')
                    ->relationship('categorieDecision', 'libelle'),

                Tables\Filters\SelectFilter::make('type_decision_id')
                    ->label('Type')
                    ->relationship('typeDecision', 'libelle'),

                Tables\Filters\SelectFilter::make('mode_composition')
                    ->label('Mode de composition')
                    ->options([
                        'juge_unique' => 'Juge unique',
                        'college' => 'Collégialité',
                    ]),

                Tables\Filters\SelectFilter::make('tribunal_id')
                    ->label('Tribunal')
                    ->relationship('tribunal', 'nom'),
            ])
            ->actions([
                // ✅ APERÇU PDF RAPIDE
                Tables\Actions\Action::make('apercu_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn($record) => route('decisions.etat.apercu', ['decision' => $record]))
                    ->openUrlInNewTab(),

                // ✅ ACTIONS DE WORKFLOW
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->peutEtreValidee())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['statut' => 'validee']);

                        \Filament\Notifications\Notification::make()
                            ->title('Décision validée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => $record->estModifiable()),
                    // ✅ TÉLÉCHARGER PDF
                    Tables\Actions\Action::make('telecharger_pdf')
                        ->label('Télécharger PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn($record) => route('decisions.etat.telecharger', ['decision' => $record]))
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => $record->estModifiable()),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_decision', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDecisions::route('/'),
            'create' => Pages\CreateDecision::route('/create'),
            'edit' => Pages\EditDecision::route('/{record}/edit'),
            'view' => Pages\ViewDecision::route('/{record}'),
        ];
    }
}