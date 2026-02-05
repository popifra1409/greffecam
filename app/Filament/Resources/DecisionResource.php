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

class DecisionResource extends Resource
{
    protected static ?string $model = Decision::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Décision';

    protected static ?string $pluralModelLabel = 'Décisions';

    protected static ?int $navigationSort = 1;

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

                                        Forms\Components\TextInput::make('numero_parquet')
                                            ->label('Numéro Parquet')
                                            ->maxLength(255),

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

                                        Forms\Components\Select::make('tribunal_id')
                                            ->label('Tribunal')
                                            ->relationship('tribunal', 'nom')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Dates')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_decision')
                                            ->label('Date de décision')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
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
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Composition du Tribunal')
                            ->schema([
                                Forms\Components\TextInput::make('president')
                                    ->label('Président')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('juge_1')
                                    ->label('Juge 1')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('juge_2')
                                    ->label('Juge 2')
                                    ->maxLength(255),

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
                                    ->label('Infractions')
                                    ->relationship('infractions', 'libelle')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),

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

                                Forms\Components\TextInput::make('montant_amende')
                                    ->label('Montant de l\'amende (FCFA)')
                                    ->numeric()
                                    ->prefix('FCFA'),

                                Forms\Components\TextInput::make('duree_peine')
                                    ->label('Durée de la peine')
                                    ->maxLength(255)
                                    ->placeholder('Ex: 2 ans'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Parties')
                            ->schema([
                                Forms\Components\Repeater::make('parties')
                                    ->relationship('parties')
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Type de partie')
                                            ->options([
                                                'prevenu' => 'Prévenu',
                                                'victime' => 'Victime',
                                                'partie_civile' => 'Partie civile',
                                                'temoin' => 'Témoin',
                                            ])
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
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('prenom')
                                                    ->label('Prénom')
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\DatePicker::make('date_naissance')
                                                    ->label('Date de naissance')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->visible(fn(Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('lieu_naissance')
                                                    ->label('Lieu de naissance')
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('profession')
                                                    ->label('Profession')
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get) => !$get('is_personne_morale')),

                                                Forms\Components\TextInput::make('nationalite')
                                                    ->label('Nationalité')
                                                    ->maxLength(255)
                                                    ->default('Camerounaise')
                                                    ->visible(fn(Get $get) => !$get('is_personne_morale')),

                                                // Personne morale
                                                Forms\Components\TextInput::make('raison_sociale')
                                                    ->label('Raison sociale')
                                                    ->required(fn(Get $get) => $get('is_personne_morale'))
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get) => $get('is_personne_morale')),

                                                Forms\Components\TextInput::make('representant_legal')
                                                    ->label('Représentant légal')
                                                    ->maxLength(255)
                                                    ->visible(fn(Get $get) => $get('is_personne_morale')),
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
                                Forms\Components\Select::make('statut')
                                    ->label('Statut')
                                    ->options([
                                        'brouillon' => 'Brouillon',
                                        'en_attente_signature' => 'En attente de signature',
                                        'signee' => 'Signée',
                                        'enregistree' => 'Enregistrée',
                                        'archivee' => 'Archivée',
                                    ])
                                    ->default('brouillon')
                                    ->required(),

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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
