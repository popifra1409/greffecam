<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DecisionResource\Pages;
use App\Models\Decision;
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

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Décision';

    protected static ?string $pluralModelLabel = 'Décisions';

    protected static ?int $navigationSort = 4;


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        // Si admin ou greffier en chef, voir tout
        if ($user->hasAnyRole(['Administrateur', 'Greffier en Chef'])) {
            return $query;
        }

        // Sinon, voir uniquement les décisions dont on est :
        // - Le détenteur actuel
        // - Le greffier responsable
        return $query->where(function ($q) use ($user) {
            $q->where('detenteur_actuel_id', $user->id)
                ->orWhere('greffier_responsable_id', $user->id);
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations Générales')
                            ->schema([
                                Forms\Components\Section::make('Identification')
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_rg')
                                            ->label('Numéro RG')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('Ex: RG/2025/001'),

                                        Forms\Components\TextInput::make('numero_repertoire')
                                            ->label('N° Répertoire')
                                            ->maxLength(255)
                                            ->placeholder('Numéro de la décision'),

                                        Forms\Components\TextInput::make('numero_parquet')
                                            ->label('Numéro Parquet')
                                            ->maxLength(255),

                                        Forms\Components\Select::make('annee_judiciaire_id')
                                            ->label('Année judiciaire')
                                            ->relationship('anneeJudiciaire', 'libelle', function ($query) {
                                                return $query->where('is_active', true)
                                                    ->orWhere('is_cloturee', false);
                                            })
                                            ->default(function () {
                                                return \App\Models\AnneeJudiciaire::where('is_active', true)->first()?->id;
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('tribunal_id')
                                            ->label('Tribunal')
                                            ->relationship('tribunal', 'nom')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('section_id')
                                            ->label('Section')
                                            ->relationship('section', 'libelle')
                                    ])->columns(3),

                                Forms\Components\Section::make('Nature de la décision')
                                    ->schema([
                                        Forms\Components\Select::make('nature_decision_id')
                                            ->label('Nature de décision')
                                            ->relationship('natureDecision', 'libelle')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('libelle')
                                                    ->required(),
                                                Forms\Components\TextInput::make('code')
                                                    ->required(),
                                            ]),
                                        Forms\Components\Select::make('statut')
                                            ->label('Statut')
                                            ->options([
                                                'brouillon' => 'Brouillon',
                                                'transmise_chef' => 'Transmise au chef',
                                                'signee' => 'Signée',
                                                'enregistree' => 'Enregistrée',
                                                'annulee' => 'Annulée',
                                                'archivee' => 'Archivée',
                                            ])
                                            ->default('brouillon')
                                            ->required()
                                            ->disabled(fn($record) => $record && $record->statut !== 'brouillon'),
                                    ])->columns(3),

                                Forms\Components\Section::make('Dates')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_decision')
                                            ->label('Date de décision')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->default(now()),

                                        Forms\Components\DateTimePicker::make('date_saisie')
                                            ->label('Date de saisie')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->default(now()),

                                        Forms\Components\DatePicker::make('date_factum')
                                            ->label('Date du factum')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\DatePicker::make('date_signature')
                                            ->label('Date de signature')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\DatePicker::make('date_enregistrement')
                                            ->label('Date d\'enregistrement')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Composition du Tribunal')
                            ->schema([
                                Forms\Components\TextInput::make('president')
                                    ->label('Président')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('juge_1')
                                    ->label(function (callable $get) {
                                        $sectionId = $get('section_id');
                                        if ($sectionId) {
                                            $section = \App\Models\Section::find($sectionId);
                                            return $section?->utilise_assesseur ? 'Assesseur 1' : 'Juge 1';
                                        }
                                        return 'Juge 1';
                                    })
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('juge_2')
                                    ->label(function (callable $get) {
                                        $sectionId = $get('section_id');
                                        if ($sectionId) {
                                            $section = \App\Models\Section::find($sectionId);
                                            return $section?->utilise_assesseur ? 'Assesseur 2' : 'Juge 2';
                                        }
                                        return 'Juge 2';
                                    })
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('assesseur')
                                    ->label('Assesseur supplémentaire')
                                    ->maxLength(255)
                                    ->visible(function (callable $get) {
                                        $sectionId = $get('section_id');
                                        if ($sectionId) {
                                            $section = \App\Models\Section::find($sectionId);
                                            return $section?->utilise_assesseur ?? false;
                                        }
                                        return false;
                                    }),

                                Forms\Components\TextInput::make('greffier')
                                    ->label('Greffier')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('ministere_public')
                                    ->label('Ministère Public')
                                    ->maxLength(255),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Détails & Décision')
                            ->schema([
                                Forms\Components\Select::make('infractions')
                                    ->label('Infractions / Nature du différend')
                                    ->relationship('infractions', 'libelle')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('libelle')
                                            ->label('Libellé')
                                            ->required(),
                                        Forms\Components\TextInput::make('code')
                                            ->label('Code')
                                            ->required(),
                                        Forms\Components\Select::make('categorie')
                                            ->label('Catégorie')
                                            ->options([
                                                'Crime' => 'Crime',
                                                'Délit' => 'Délit',
                                                'Contravention' => 'Contravention',
                                            ])
                                            ->required(),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Description')
                                            ->rows(2),
                                    ])
                                    ->columnSpanFull()
                                    ->helperText('Sélectionnez les infractions ou la nature du différend'),

                                Forms\Components\Section::make('Faits et dispositif')
                                    ->schema([
                                        Forms\Components\Textarea::make('resume')
                                            ->label('Résumé des faits')
                                            ->maxLength(65535)
                                            ->rows(4)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('dispositif')
                                            ->label('Dispositif')
                                            ->maxLength(65535)
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),

                                Forms\Components\Section::make('Condamnation pécuniaire')
                                    ->schema([
                                        Forms\Components\TextInput::make('montant_amende')
                                            ->label('Montant de l\'amende')
                                            ->numeric()
                                            ->prefix('FCFA')
                                            ->maxValue(9999999999999.99)
                                            ->placeholder('0'),

                                        Forms\Components\TextInput::make('montant_depens')
                                            ->label('Montant des dépens')
                                            ->numeric()
                                            ->prefix('FCFA')
                                            ->maxValue(9999999999999.99)
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
                        Forms\Components\Tabs\Tab::make('Parties')
                            ->schema([
                                Forms\Components\Repeater::make('parties')
                                    ->relationship('parties')
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Type de partie')
                                            ->options(function (callable $get) {
                                                $sectionId = $get('../../section_id');
                                                if ($sectionId) {
                                                    $section = \App\Models\Section::find($sectionId);
                                                    return $section?->types_parties ?? [];
                                                }
                                                return [
                                                    'demandeur' => 'Demandeur',
                                                    'defendeur' => 'Défendeur',
                                                    'temoin' => 'Témoin',
                                                ];
                                            })
                                            ->required()
                                            ->live(),

                                        Forms\Components\Toggle::make('is_personne_morale')
                                            ->label('Personne morale')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\Section::make('Identité')
                                            ->schema([
                                                // Personne physique
                                                Forms\Components\TextInput::make('nom')
                                                    ->label('Nom')
                                                    ->required(fn(Forms\Get $get) => !$get('is_personne_morale'))
                                                    ->maxLength(255)
                                                    ->visible(fn(Forms\Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('prenom')
                                                    ->label('Prénom')
                                                    ->maxLength(255)
                                                    ->visible(fn(Forms\Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\DatePicker::make('date_naissance')
                                                    ->label('Date de naissance')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->visible(fn(Forms\Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('lieu_naissance')
                                                    ->label('Lieu de naissance')
                                                    ->maxLength(255)
                                                    ->visible(fn(Forms\Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('profession')
                                                    ->label('Profession')
                                                    ->maxLength(255)
                                                    ->visible(fn(Forms\Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('nationalite')
                                                    ->label('Nationalité')
                                                    ->maxLength(255)
                                                    ->default('Camerounaise')
                                                    ->visible(fn(Forms\Get $get) => !$get('is_personne_morale')),

                                                // Personne morale
                                                Forms\Components\TextInput::make('raison_sociale')
                                                    ->label('Raison sociale')
                                                    ->required(fn(Forms\Get $get) => $get('is_personne_morale'))
                                                    ->maxLength(255)
                                                    ->visible(fn(Forms\Get $get) => $get('is_personne_morale')),

                                                Forms\Components\TextInput::make('representant_legal')
                                                    ->label('Représentant légal')
                                                    ->maxLength(255)
                                                    ->visible(fn(Forms\Get $get) => $get('is_personne_morale')),
                                            ])->columns(2),

                                        Forms\Components\Section::make('Contact')
                                            ->schema([
                                                Forms\Components\Textarea::make('adresse')
                                                    ->label('Adresse')
                                                    ->maxLength(65535)
                                                    ->rows(2)
                                                    ->columnSpanFull(),

                                                Forms\Components\TextInput::make('telephone')
                                                    ->label('Téléphone')
                                                    ->tel()
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->maxLength(255),
                                            ])->columns(2),

                                        Forms\Components\Section::make('Avocat')
                                            ->schema([
                                                Forms\Components\TextInput::make('avocat_nom')
                                                    ->label('Nom de l\'avocat')
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('avocat_contact')
                                                    ->label('Contact de l\'avocat')
                                                    ->maxLength(255),
                                            ])->columns(2)
                                            ->collapsed(),
                                    ])
                                    ->itemLabel(
                                        fn(array $state): ?string =>
                                        $state['is_personne_morale'] ?? false
                                            ? ($state['raison_sociale'] ?? 'Nouvelle partie')
                                            : (($state['nom'] ?? '') . ' ' . ($state['prenom'] ?? '') ?: 'Nouvelle partie')
                                    )
                                    ->collapsible()
                                    ->cloneable()
                                    ->reorderable()
                                    ->addActionLabel('Ajouter une partie')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Gestion')
                            ->schema([
                                Forms\Components\Select::make('greffier_responsable_id')
                                    ->label('Greffier responsable')
                                    ->relationship('greffierResponsable', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\FileUpload::make('fichier_scan')
                                    ->label('Fichier scanné (PDF)')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->directory('decisions/scans')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_rg')
                    ->label('N° RG')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('date_decision')
                    ->label('Date décision')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('natureDecision.libelle')
                    ->label('Nature')
                    ->searchable()
                    ->badge()
                    ->wrap(),

                Tables\Columns\TextColumn::make('tribunal.ville')
                    ->label('Tribunal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
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
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('greffierResponsable.name')
                    ->label('Greffier')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'en_attente_signature' => 'En attente de signature',
                        'signee' => 'Signée',
                        'enregistree' => 'Enregistrée',
                        'archivee' => 'Archivée',
                    ]),

                Tables\Filters\SelectFilter::make('nature_decision_id')
                    ->label('Nature de décision')
                    ->relationship('natureDecision', 'libelle'),

                Tables\Filters\SelectFilter::make('tribunal_id')
                    ->label('Tribunal')
                    ->relationship('tribunal', 'nom'),

                Tables\Filters\Filter::make('date_decision')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn($q, $date) => $q->whereDate('date_decision', '>=', $date))
                            ->when($data['date_to'], fn($q, $date) => $q->whereDate('date_decision', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->peutEtreValidee())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'validee',
                            'date_validation' => now(),
                            'validee_par' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Décision validée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('transmettre')
                    ->label('Transmettre')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn($record) => $record->peutEtreTransmise() && $record->detenteur_actuel_id === auth()->id())
                    ->form([
                        Forms\Components\Select::make('destinataire_id')
                            ->label('Transmettre à')
                            ->options(function () {
                                // Récupérer les utilisateurs hiérarchiques (Greffier en Chef, Admins, Juges)
                                return \App\Models\User::role(['Administrateur', 'Greffier en Chef', 'Juge'])
                                    ->where('id', '!=', auth()->id())
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Sélectionnez l\'utilisateur auquel transmettre la décision'),

                        Forms\Components\Select::make('motif')
                            ->label('Motif de la transmission')
                            ->options([
                                'validation' => 'Pour validation',
                                'signature' => 'Pour signature',
                                'correction' => 'Pour correction',
                                'avis' => 'Pour avis',
                                'information' => 'Pour information',
                                'autre' => 'Autre',
                            ])
                            ->required()
                            ->default('validation'),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observations / Instructions')
                            ->rows(3)
                            ->placeholder('Ajoutez vos observations ou instructions...'),
                    ])
                    ->action(function ($record, array $data) {
                        // Créer la transmission
                        \App\Models\TransmissionDecision::create([
                            'decision_id' => $record->id,
                            'expediteur_id' => auth()->id(),
                            'destinataire_id' => $data['destinataire_id'],
                            'motif' => $data['motif'],
                            'observations_expediteur' => $data['observations'] ?? null,
                            'date_transmission' => now(),
                            'statut' => 'en_attente',
                        ]);

                        // Mettre à jour la décision
                        $record->update([
                            'statut' => 'transmise_chef',
                            'detenteur_actuel_id' => $data['destinataire_id'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Décision transmise')
                            ->body('La décision a été transmise avec succès')
                            ->success()
                            ->send();

                        // TODO: Envoyer une notification au destinataire
                    }),

                Tables\Actions\Action::make('traiter_transmission')
                    ->label('Traiter')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(function ($record) {
                        // Visible si l'utilisateur est le détenteur actuel et la décision est transmise
                        return $record->statut === 'transmise_chef' && $record->detenteur_actuel_id === auth()->id();
                    })
                    ->form([
                        Forms\Components\Select::make('action')
                            ->label('Action')
                            ->options([
                                'accepter' => 'Accepter et signer',
                                'rejeter' => 'Rejeter / Demander corrections',
                                'retourner' => 'Retourner à l\'expéditeur',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observations')
                            ->required(fn(Forms\Get $get) => in_array($get('action'), ['rejeter', 'retourner']))
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        // Récupérer la dernière transmission en attente
                        $transmission = $record->transmissions()
                            ->where('statut', 'en_attente')
                            ->where('destinataire_id', auth()->id())
                            ->latest()
                            ->first();

                        if (!$transmission) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Aucune transmission en attente trouvée')
                                ->danger()
                                ->send();
                            return;
                        }

                        switch ($data['action']) {
                            case 'accepter':
                                $transmission->update([
                                    'statut' => 'acceptee',
                                    'observations_destinataire' => $data['observations'] ?? null,
                                    'date_traitement' => now(),
                                ]);

                                $record->update([
                                    'statut' => 'signee',
                                    'date_signature' => now(),
                                ]);

                                \Filament\Notifications\Notification::make()
                                    ->title('Décision signée')
                                    ->success()
                                    ->send();
                                break;

                            case 'rejeter':
                                $transmission->update([
                                    'statut' => 'rejetee',
                                    'observations_destinataire' => $data['observations'],
                                    'date_traitement' => now(),
                                ]);

                                $record->update([
                                    'statut' => 'rejetee',
                                    'detenteur_actuel_id' => $transmission->expediteur_id,
                                ]);

                                \Filament\Notifications\Notification::make()
                                    ->title('Décision rejetée')
                                    ->body('La décision a été retournée pour corrections')
                                    ->warning()
                                    ->send();
                                break;

                            case 'retourner':
                                $transmission->update([
                                    'statut' => 'retournee',
                                    'observations_destinataire' => $data['observations'],
                                    'date_traitement' => now(),
                                ]);

                                $record->update([
                                    'statut' => 'brouillon',
                                    'detenteur_actuel_id' => $transmission->expediteur_id,
                                ]);

                                \Filament\Notifications\Notification::make()
                                    ->title('Décision retournée')
                                    ->warning()
                                    ->send();
                                break;
                        }
                    }),

                Tables\Actions\Action::make('signer')
                    ->label('Signer')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->visible(fn($record) => $record->peutEtreSignee())
                    ->requiresConfirmation()
                    ->modalHeading('Signer la décision')
                    ->modalDescription('Confirmez-vous la signature de cette décision ?')
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

                Tables\Actions\Action::make('enregistrer')
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

                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->peutEtreAnnulee())
                    ->form([
                        Forms\Components\Textarea::make('motif_annulation')
                            ->label('Motif d\'annulation')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'statut' => 'annulee',
                            'motif_annulation' => $data['motif_annulation'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Décision annulée')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->estModifiable()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->estModifiable()),
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
