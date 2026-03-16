<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DecisionResource\Pages;
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

    protected static ?int $navigationSort = 4;

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
                                        "{$record->numero_dossier} - {$record->demandeur_nom_complet} vs {$record->defendeur_nom_complet}"
                                    )
                                    ->searchable(['numero_dossier', 'demandeur_nom', 'defendeur_nom'])
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

                                // ✅ INFOS DU DOSSIER (LECTURE SEULE)
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
                                                        '<strong>Demandeur :</strong> ' . $dossier->demandeur_nom_complet . '<br>' .
                                                        '<strong>Défendeur :</strong> ' . $dossier->defendeur_nom_complet . '<br>' .
                                                        '<strong>Infractions :</strong> ' . $dossier->infractions->pluck('libelle')->join(', ') . '<br>' .
                                                        '<strong>Date enrôlement :</strong> ' . $dossier->date_enrolement?->format('d/m/Y') .
                                                        '</div>'
                                                );
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn(Get $get) => $get('dossier_id'))
                                    ->collapsible(),

                                // Champs cachés pour stocker les IDs
                                Forms\Components\Hidden::make('tribunal_id'),
                                Forms\Components\Hidden::make('section_id'),
                                Forms\Components\Hidden::make('matiere_id'),
                                Forms\Components\Hidden::make('annee_judiciaire_id'),
                            ]),

                        // ✅ ONGLET 2 : NUMÉROS ET DATES
                        Forms\Components\Tabs\Tab::make('Identification')
                            ->schema([
                                Forms\Components\Section::make('Numéros')
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_rg')
                                            ->label('Numéro RG')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('Ex: RG/2025/001'),

                                        Forms\Components\TextInput::make('numero_repertoire')
                                            ->label('N° Répertoire / N° Décision')
                                            ->maxLength(255)
                                            ->placeholder('Numéro de la décision'),

                                        Forms\Components\TextInput::make('numero_parquet')
                                            ->label('Numéro Parquet')
                                            ->maxLength(255)
                                            ->placeholder('Référence du parquet'),
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

                                Forms\Components\Section::make('Nature de la décision')
                                    ->schema([
                                        Forms\Components\Select::make('nature_decision_id')
                                            ->label('Nature de décision')
                                            ->relationship('natureDecision', 'libelle')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

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
                                    ])->columns(2),
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

                                // ✅ SI JUGE UNIQUE
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

                                // ✅ SI COLLÈGE
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

                                // ✅ GREFFIER
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

                        // ✅ ONGLET 4 : CONTENU DE LA DÉCISION
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

                        // ✅ ONGLET 5 : GESTION
                        Forms\Components\Tabs\Tab::make('Gestion')
                            ->schema([
                                Forms\Components\Select::make('greffier_responsable_id')
                                    ->label('Greffier responsable du dossier')
                                    ->relationship('greffierResponsable', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Greffier chargé du suivi de la décision'),

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
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->label('N° Dossier')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('numero_rg')
                    ->label('N° RG')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('numero_repertoire')
                    ->label('N° Décision')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_decision')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('natureDecision.libelle')
                    ->label('Nature')
                    ->searchable()
                    ->badge()
                    ->wrap(),

                Tables\Columns\TextColumn::make('composition')
                    ->label('Composition')
                    ->getStateUsing(fn($record) => $record->composition)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tribunal.nom')
                    ->label('Tribunal')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'transmise_chef' => 'Transmise',
                        'signee' => 'Signée',
                        'enregistree' => 'Enregistrée',
                        'annulee' => 'Annulée',
                        'archivee' => 'Archivée',
                        default => $state,
                    })
                    ->sortable(),

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
                        'transmise_chef' => 'Transmise au chef',
                        'signee' => 'Signée',
                        'enregistree' => 'Enregistrée',
                        'archivee' => 'Archivée',
                    ]),

                Tables\Filters\SelectFilter::make('mode_composition')
                    ->label('Mode de composition')
                    ->options([
                        'juge_unique' => 'Juge unique',
                        'college' => 'Collégialité',
                    ]),

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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
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
