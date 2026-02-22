<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DossierResource\Pages;
use App\Models\Dossier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class DossierResource extends Resource
{
    protected static ?string $model = Dossier::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Dossier';

    protected static ?string $pluralModelLabel = 'Dossiers / Enrôlement';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Hiérarchie judiciaire')
                        ->schema([
                            Forms\Components\Section::make('Localisation du dossier')
                                ->schema([
                                    Forms\Components\Select::make('tribunal_id')
                                        ->label('Tribunal')
                                        ->relationship('tribunal', 'nom')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set) {
                                            $set('section_id', null);
                                            $set('matiere_id', null);
                                        }),

                                    Forms\Components\Select::make('section_id')
                                        ->label('Section')
                                        ->relationship('section', 'libelle')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(fn(callable $set) => $set('matiere_id', null)),

                                    Forms\Components\Select::make('matiere_id')
                                        ->label('Matière')
                                        ->options(function (callable $get) {
                                            $sectionId = $get('section_id');
                                            if ($sectionId) {
                                                return \App\Models\Matiere::where('section_id', $sectionId)
                                                    ->where('is_active', true)
                                                    ->pluck('designation', 'id');
                                            }
                                            return [];
                                        })
                                        ->required()
                                        ->searchable(),

                                    Forms\Components\Select::make('annee_judiciaire_id')
                                        ->label('Année judiciaire')
                                        ->relationship('anneeJudiciaire', 'libelle', function ($query) {
                                            return $query->where('is_active', true);
                                        })
                                        ->default(function () {
                                            return \App\Models\AnneeJudiciaire::where('is_active', true)->first()?->id;
                                        })
                                        ->required(),

                                    Forms\Components\DatePicker::make('date_enrolement')
                                        ->label('Date d\'enrôlement')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->default(now()),
                                ])->columns(2),
                        ]),

                    Forms\Components\Wizard\Step::make('Demandeur')
                        ->schema([
                            Forms\Components\Section::make('Identité du demandeur')
                                ->schema([
                                    Forms\Components\Toggle::make('demandeur_est_personne_morale')
                                        ->label('Personne morale')
                                        ->live()
                                        ->columnSpanFull(),

                                    // Personne physique
                                    Forms\Components\TextInput::make('demandeur_nom')
                                        ->label('Nom')
                                        ->required(fn(Get $get) => !$get('demandeur_est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('demandeur_prenom')
                                        ->label('Prénom')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale')),

                                    Forms\Components\DatePicker::make('demandeur_date_naissance')
                                        ->label('Date de naissance')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('demandeur_lieu_naissance')
                                        ->label('Lieu de naissance')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('demandeur_profession')
                                        ->label('Profession')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('demandeur_nationalite')
                                        ->label('Nationalité')
                                        ->maxLength(255)
                                        ->default('Camerounaise')
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale')),

                                    // Personne morale
                                    Forms\Components\TextInput::make('demandeur_raison_sociale')
                                        ->label('Raison sociale')
                                        ->required(fn(Get $get) => $get('demandeur_est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('demandeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('demandeur_representant_legal')
                                        ->label('Représentant légal')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('demandeur_est_personne_morale')),
                                ])->columns(2),

                            Forms\Components\Section::make('Contact du demandeur')
                                ->schema([
                                    Forms\Components\Textarea::make('demandeur_adresse')
                                        ->label('Adresse')
                                        ->maxLength(65535)
                                        ->rows(2)
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('demandeur_telephone')
                                        ->label('Téléphone')
                                        ->tel()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('demandeur_email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                ])->columns(2),

                            Forms\Components\Section::make('Avocat du demandeur')
                                ->schema([
                                    Forms\Components\TextInput::make('avocat_demandeur_nom')
                                        ->label('Nom de l\'avocat')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('avocat_demandeur_contact')
                                        ->label('Contact de l\'avocat')
                                        ->maxLength(255),
                                ])->columns(2)
                                ->collapsible()
                                ->collapsed(),
                        ]),

                    Forms\Components\Wizard\Step::make('Observations')
                        ->schema([
                            Forms\Components\Textarea::make('observations')
                                ->label('Observations')
                                ->maxLength(65535)
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_dossier')
                    ->label('N° Dossier')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('demandeur_nom_complet')
                    ->label('Demandeur')
                    ->getStateUsing(fn($record) => $record->demandeur_nom_complet)
                    ->searchable(['demandeur_nom', 'demandeur_prenom', 'demandeur_raison_sociale'])
                    ->wrap(),

                Tables\Columns\TextColumn::make('matiere.designation')
                    ->label('Matière')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('section.libelle')
                    ->label('Section')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('date_enrolement')
                    ->label('Enrôlé le')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('statut')
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
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('decisions_count')
                    ->label('Décisions')
                    ->counts('decisions')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tribunal_id')
                    ->label('Tribunal')
                    ->relationship('tribunal', 'nom'),

                Tables\Filters\SelectFilter::make('section_id')
                    ->label('Section')
                    ->relationship('section', 'libelle'),

                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'ouvert' => 'Ouvert',
                        'en_instance' => 'En instance',
                        'grosse_delivree' => 'Grosse délivrée',
                        'en_recours' => 'En recours',
                        'clos' => 'Clos',
                    ]),
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
            ->defaultSort('date_enrolement', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDossiers::route('/'),
            'create' => Pages\CreateDossier::route('/create'),
            'edit' => Pages\EditDossier::route('/{record}/edit'),
            'view' => Pages\ViewDossier::route('/{record}'),
        ];
    }
}
