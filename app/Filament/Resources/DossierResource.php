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

    protected static ?int $navigationSort = 1;

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
                                            $set('type_section', null);
                                        }),

                                    Forms\Components\Select::make('section_id')
                                        ->label('Section')
                                        ->relationship('section', 'libelle')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            $set('matiere_id', null);

                                            // Récupérer le type de section
                                            if ($state) {
                                                $section = \App\Models\Section::find($state);
                                                if ($section) {
                                                    $set('type_section', $section->type);
                                                }
                                            } else {
                                                $set('type_section', null);
                                            }
                                        }),

                                    Forms\Components\Hidden::make('type_section'),
                                    Forms\Components\Placeholder::make('debug_type_section')
                                        ->label('Type de section détecté')
                                        ->content(fn(callable $get) => $get('type_section')
                                            ? ($get('type_section') === 'repressive' ? '🔴 Section Répressive' : '🟢 Section Non Répressive')
                                            : 'Aucune section sélectionnée')
                                        ->columnSpanFull(),

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

                                    Forms\Components\DatePicker::make('date_assignation')
                                        ->label('Date d\'assignation')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->helperText('Date à laquelle le défendeur/prévenu a été assigné'),
                                ])->columns(2),
                        ]),

                    Forms\Components\Wizard\Step::make('Partie requérante')
                        ->schema([
                            Forms\Components\Section::make('Identité')
                                ->description(fn(callable $get) => $get('type_section') === 'repressive'
                                    ? 'Ministère Public (Partie poursuivante)'
                                    : 'Demandeur (Partie requérante)')
                                ->schema([
                                    // Pour section répressive, afficher info Ministère Public
                                    Forms\Components\Placeholder::make('ministere_public_info')
                                        ->label('Ministère Public')
                                        ->content('Le Ministère Public est représenté d\'office. Vous pouvez ajouter une partie civile dans les observations.')
                                        ->visible(fn(callable $get) => $get('type_section') === 'repressive')
                                        ->columnSpanFull(),

                                    Forms\Components\Toggle::make('demandeur_est_personne_morale')
                                        ->label('Personne morale')
                                        ->live()
                                        ->columnSpanFull()
                                        ->visible(fn(callable $get) => $get('type_section') !== 'repressive'),

                                    // Personne physique (non répressive uniquement)
                                    Forms\Components\TextInput::make('demandeur_nom')
                                        ->label('Nom')
                                        ->required(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    Forms\Components\TextInput::make('demandeur_prenom')
                                        ->label('Prénom')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    Forms\Components\DatePicker::make('demandeur_date_naissance')
                                        ->label('Date de naissance')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    Forms\Components\TextInput::make('demandeur_lieu_naissance')
                                        ->label('Lieu de naissance')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    Forms\Components\TextInput::make('demandeur_profession')
                                        ->label('Profession')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    Forms\Components\TextInput::make('demandeur_nationalite')
                                        ->label('Nationalité')
                                        ->maxLength(255)
                                        ->default('Camerounaise')
                                        ->visible(fn(Get $get) => !$get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    // Personne morale (non répressive uniquement)
                                    Forms\Components\TextInput::make('demandeur_raison_sociale')
                                        ->label('Raison sociale')
                                        ->required(fn(Get $get) => $get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),

                                    Forms\Components\TextInput::make('demandeur_representant_legal')
                                        ->label('Représentant légal')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('demandeur_est_personne_morale') && $get('type_section') !== 'repressive'),
                                ])->columns(2),

                            Forms\Components\Section::make('Contact')
                                ->visible(fn(callable $get) => $get('type_section') !== 'repressive')
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

                            Forms\Components\Section::make('Avocat')
                                ->visible(fn(callable $get) => $get('type_section') !== 'repressive')
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

                    Forms\Components\Wizard\Step::make('Partie adverse')
                        ->schema([
                            Forms\Components\Section::make('Identité')
                                ->description(fn(callable $get) => $get('type_section') === 'repressive'
                                    ? 'Prévenu (Personne poursuivie)'
                                    : 'Défendeur (Partie adverse)')
                                ->schema([
                                    Forms\Components\Toggle::make('defendeur_est_personne_morale')
                                        ->label('Personne morale')
                                        ->live()
                                        ->columnSpanFull(),

                                    // Personne physique
                                    Forms\Components\TextInput::make('defendeur_nom')
                                        ->label(fn(callable $get) => $get('type_section') === 'repressive' ? 'Nom du prévenu' : 'Nom')
                                        ->required(fn(Get $get) => !$get('defendeur_est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('defendeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('defendeur_prenom')
                                        ->label(fn(callable $get) => $get('type_section') === 'repressive' ? 'Prénom du prévenu' : 'Prénom')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('defendeur_est_personne_morale')),

                                    Forms\Components\DatePicker::make('defendeur_date_naissance')
                                        ->label('Date de naissance')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn(Get $get) => !$get('defendeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('defendeur_lieu_naissance')
                                        ->label('Lieu de naissance')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('defendeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('defendeur_profession')
                                        ->label('Profession')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('defendeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('defendeur_nationalite')
                                        ->label('Nationalité')
                                        ->maxLength(255)
                                        ->default('Camerounaise')
                                        ->visible(fn(Get $get) => !$get('defendeur_est_personne_morale')),

                                    // Personne morale
                                    Forms\Components\TextInput::make('defendeur_raison_sociale')
                                        ->label('Raison sociale')
                                        ->required(fn(Get $get) => $get('defendeur_est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('defendeur_est_personne_morale')),

                                    Forms\Components\TextInput::make('defendeur_representant_legal')
                                        ->label('Représentant légal')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('defendeur_est_personne_morale')),
                                ])->columns(2),

                            Forms\Components\Section::make('Contact')
                                ->schema([
                                    Forms\Components\Textarea::make('defendeur_adresse')
                                        ->label('Adresse')
                                        ->maxLength(65535)
                                        ->rows(2)
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('defendeur_telephone')
                                        ->label('Téléphone')
                                        ->tel()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('defendeur_email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                ])->columns(2),

                            Forms\Components\Section::make('Avocat')
                                ->schema([
                                    Forms\Components\TextInput::make('avocat_defendeur_nom')
                                        ->label('Nom de l\'avocat')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('avocat_defendeur_contact')
                                        ->label('Contact de l\'avocat')
                                        ->maxLength(255),
                                ])->columns(2)
                                ->collapsible()
                                ->collapsed(),
                        ]),

                    Forms\Components\Wizard\Step::make('Objet du différend')
                        ->schema([
                            Forms\Components\Section::make('Infractions / Nature du différend')
                                ->schema([
                                    Forms\Components\Select::make('infractions')
                                        ->label(fn(callable $get) => $get('type_section') === 'repressive'
                                            ? 'Infractions poursuivies'
                                            : 'Objet du différend')
                                        ->relationship('infractions', 'libelle')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->required()
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
                                        ->helperText(fn(callable $get) => $get('type_section') === 'repressive'
                                            ? 'Sélectionnez les infractions reprochées au prévenu'
                                            : 'Sélectionnez ou décrivez la nature du différend')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Observations')
                        ->schema([
                            Forms\Components\Textarea::make('observations')
                                ->label('Observations')
                                ->maxLength(65535)
                                ->rows(4)
                                ->columnSpanFull()
                                ->helperText('Remarques complémentaires, partie civile (pour section répressive), etc.'),
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
                    ->label('Demandeur / MP')
                    ->getStateUsing(fn($record) => $record->type_section === 'repressive' ? 'Ministère Public' : $record->demandeur_nom_complet)
                    ->searchable(['demandeur_nom', 'demandeur_prenom', 'demandeur_raison_sociale'])
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('defendeur_nom_complet')
                    ->label('Défendeur / Prévenu')
                    ->getStateUsing(fn($record) => $record->defendeur_nom_complet ?: '-')
                    ->searchable(['defendeur_nom', 'defendeur_prenom', 'defendeur_raison_sociale'])
                    ->wrap()
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('matiere.designation')
                    ->label('Matière')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->wrap(),

                Tables\Columns\TextColumn::make('section.libelle')
                    ->label('Section')
                    ->searchable()
                    ->badge()
                    ->color(fn($record) => $record->section?->type === 'repressive' ? 'danger' : 'success')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tribunal.nom')
                    ->label('Tribunal')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('date_enrolement')
                    ->label('Enrôlé le')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_assignation')
                    ->label('Assigné le')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

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

                Tables\Filters\SelectFilter::make('matiere_id')
                    ->label('Matière')
                    ->relationship('matiere', 'designation'),

                Tables\Filters\SelectFilter::make('type_section')
                    ->label('Type de section')
                    ->options([
                        'repressive' => 'Répressive',
                        'non_repressive' => 'Non Répressive',
                    ]),

                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'ouvert' => 'Ouvert',
                        'en_instance' => 'En instance',
                        'grosse_delivree' => 'Grosse délivrée',
                        'en_recours' => 'En recours',
                        'clos' => 'Clos',
                    ]),

                Tables\Filters\Filter::make('date_enrolement')
                    ->form([
                        Forms\Components\DatePicker::make('enrole_du')
                            ->label('Enrôlé du')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('enrole_au')
                            ->label('Au')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['enrole_du'], fn($q, $date) => $q->whereDate('date_enrolement', '>=', $date))
                            ->when($data['enrole_au'], fn($q, $date) => $q->whereDate('date_enrolement', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['enrole_du'] ?? null) {
                            $indicators[] = 'Enrôlé du ' . \Carbon\Carbon::parse($data['enrole_du'])->format('d/m/Y');
                        }
                        if ($data['enrole_au'] ?? null) {
                            $indicators[] = 'Au ' . \Carbon\Carbon::parse($data['enrole_au'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),

                Tables\Filters\Filter::make('date_assignation')
                    ->form([
                        Forms\Components\DatePicker::make('assigne_du')
                            ->label('Assigné du')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('assigne_au')
                            ->label('Au')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['assigne_du'], fn($q, $date) => $q->whereDate('date_assignation', '>=', $date))
                            ->when($data['assigne_au'], fn($q, $date) => $q->whereDate('date_assignation', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['assigne_du'] ?? null) {
                            $indicators[] = 'Assigné du ' . \Carbon\Carbon::parse($data['assigne_du'])->format('d/m/Y');
                        }
                        if ($data['assigne_au'] ?? null) {
                            $indicators[] = 'Au ' . \Carbon\Carbon::parse($data['assigne_au'])->format('d/m/Y');
                        }
                        return $indicators;
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
