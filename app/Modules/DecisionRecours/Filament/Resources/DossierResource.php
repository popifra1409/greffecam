<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\DossierResource\Pages;
use App\Models\Dossier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use App\Traits\HasResourcePermissions;

class DossierResource extends Resource
{
    use HasResourcePermissions;
    protected static ?string $model = Dossier::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Dossier';

    protected static ?string $pluralModelLabel = 'Dossiers / Enrôlement';

    protected static ?int $navigationSort = 10;


    // ✅ Définir les permissions
    protected static function getViewPermission(): string
    {
        return 'view_dossiers';
    }

    protected static function getCreatePermission(): string
    {
        return 'create_dossiers';
    }

    protected static function getEditPermission(): string
    {
        return 'edit_dossiers';
    }

    protected static function getDeletePermission(): string
    {
        return 'delete_dossiers';
    }

    public static function getRelations(): array
    {
        return [
            \App\Modules\DecisionRecours\Filament\Resources\DossierResource\RelationManagers\DecisionsRelationManager::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // ✅ ÉTAPE 1 : HIÉRARCHIE JUDICIAIRE
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

                                    Forms\Components\DatePicker::make('date_premiere_audience')
                                        ->label('Date de première audience')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->helperText('Date prévue pour la première audience')
                                        ->after('date_assignation'),

                                    Forms\Components\TextInput::make('numero_dossier_personnalise')
                                        ->label('Numéro de dossier personnalisé (ancien système)')
                                        ->maxLength(255)
                                        ->helperText('Optionnel - Pour les dossiers avec ancienne numérotation')
                                        ->placeholder('Ex: RG/2020/123')
                                        ->columnSpanFull(),
                                ])->columns(2),
                        ]),

                    // ✅ ÉTAPE 2 : PARTIES REQUÉRANTES (REPEATER)
                    Forms\Components\Wizard\Step::make('Parties requérantes')
                        ->schema([
                            Forms\Components\Placeholder::make('info_requérants')
                                ->label('')
                                ->content(fn(Get $get) => $get('type_section') === 'repressive'
                                    ? '⚖️ Section répressive : Le Ministère Public est partie poursuivante. Vous pouvez ajouter une ou plusieurs parties civiles.'
                                    : '📋 Section non répressive : Ajoutez un ou plusieurs demandeurs.')
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('parties_requerantes')
                                ->label('')
                                ->relationship(
                                    'parties',
                                    modifyQueryUsing: fn($query, Get $get) =>
                                    $query->whereIn('type_partie', $get('type_section') === 'repressive'
                                        ? ['partie_civile']
                                        : ['demandeur'])
                                )
                                ->schema([
                                    Forms\Components\Hidden::make('type_partie')
                                        ->default(fn(Get $get) => $get('../../../type_section') === 'repressive' ? 'partie_civile' : 'demandeur'),

                                    Forms\Components\Toggle::make('est_personne_morale')
                                        ->label('Personne morale')
                                        ->live()
                                        ->columnSpanFull(),

                                    Forms\Components\Toggle::make('est_famille')
                                        ->label('Représente une famille')
                                        ->live()
                                        ->visible(fn(Get $get) => !$get('est_personne_morale'))
                                        ->helperText('Cochez si cette personne physique agit au nom d\'une famille (utile pour les séquestres)')
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('nom_famille')
                                        ->label('Nom de la famille')
                                        ->placeholder('Ex: NGONO')
                                        ->required(fn(Get $get) => $get('est_famille'))
                                        ->visible(fn(Get $get) => $get('est_famille') && !$get('est_personne_morale'))
                                        ->helperText('Le dossier séquestre portera le nom "Dossier Famille [nom saisi ici]"')
                                        ->columnSpanFull(),

                                    // Personne physique
                                    Forms\Components\TextInput::make('nom')
                                        ->label('Nom')
                                        ->required(fn(Get $get) => !$get('est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('prenom')
                                        ->label('Prénom')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\DatePicker::make('date_naissance')
                                        ->label('Date de naissance')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('lieu_naissance')
                                        ->label('Lieu de naissance')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('profession')
                                        ->label('Profession')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('nationalite')
                                        ->label('Nationalité')
                                        ->maxLength(255)
                                        ->default('Camerounaise')
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    // Personne morale
                                    Forms\Components\TextInput::make('raison_sociale')
                                        ->label('Raison sociale')
                                        ->required(fn(Get $get) => $get('est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('est_personne_morale')),

                                    Forms\Components\TextInput::make('representant_legal')
                                        ->label('Représentant légal')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('est_personne_morale')),

                                    // Contact
                                    Forms\Components\Textarea::make('adresse')
                                        ->label('Adresse')
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

                                    // Avocat
                                    Forms\Components\TextInput::make('avocat_nom')
                                        ->label('Avocat')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('avocat_contact')
                                        ->label('Contact avocat')
                                        ->maxLength(255),
                                ])
                                ->itemLabel(
                                    fn(array $state): ?string =>
                                    $state['est_personne_morale'] ?? false
                                        ? ($state['raison_sociale'] ?? 'Nouvelle partie')
                                        : (($state['nom'] ?? '') . ' ' . ($state['prenom'] ?? '') ?: 'Nouvelle partie')
                                )
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->addActionLabel(fn(Get $get) => $get('type_section') === 'repressive'
                                    ? 'Ajouter une partie civile'
                                    : 'Ajouter un demandeur')
                                ->minItems(fn(Get $get) => $get('type_section') === 'repressive' ? 0 : 1)
                                ->defaultItems(fn(Get $get) => $get('type_section') === 'repressive' ? 0 : 1)
                                ->columnSpanFull()
                                ->columns(2),
                        ]),

                    // ✅ ÉTAPE 3 : PARTIES ADVERSES (REPEATER)
                    Forms\Components\Wizard\Step::make('Parties adverses')
                        ->schema([
                            Forms\Components\Placeholder::make('info_adverses')
                                ->label('')
                                ->content(fn(Get $get) => $get('type_section') === 'repressive'
                                    ? '⚖️ Section répressive : Ajoutez un ou plusieurs prévenus.'
                                    : '📋 Section non répressive : Ajoutez un ou plusieurs défendeurs.')
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('parties_adverses')
                                ->label('')
                                ->relationship(
                                    'parties',
                                    modifyQueryUsing: fn($query, Get $get) =>
                                    $query->whereIn('type_partie', $get('type_section') === 'repressive'
                                        ? ['prevenu']
                                        : ['defendeur'])
                                )
                                ->schema([
                                    Forms\Components\Hidden::make('type_partie')
                                        ->default(fn(Get $get) => $get('../../../type_section') === 'repressive' ? 'prevenu' : 'defendeur'),

                                    Forms\Components\Toggle::make('est_personne_morale')
                                        ->label('Personne morale')
                                        ->live()
                                        ->columnSpanFull(),

                                    Forms\Components\Toggle::make('est_famille')
                                        ->label('Représente une famille')
                                        ->live()
                                        ->visible(fn(Get $get) => !$get('est_personne_morale'))
                                        ->helperText('Cochez si cette personne physique agit au nom d\'une famille (utile pour les séquestres)')
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('nom_famille')
                                        ->label('Nom de la famille')
                                        ->placeholder('Ex: NGONO')
                                        ->required(fn(Get $get) => $get('est_famille'))
                                        ->visible(fn(Get $get) => $get('est_famille') && !$get('est_personne_morale'))
                                        ->helperText('Le dossier séquestre portera le nom "Dossier Famille [nom saisi ici]"')
                                        ->columnSpanFull(),

                                    // Personne physique
                                    Forms\Components\TextInput::make('nom')
                                        ->label('Nom')
                                        ->required(fn(Get $get) => !$get('est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('prenom')
                                        ->label('Prénom')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\DatePicker::make('date_naissance')
                                        ->label('Date de naissance')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('lieu_naissance')
                                        ->label('Lieu de naissance')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('profession')
                                        ->label('Profession')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    Forms\Components\TextInput::make('nationalite')
                                        ->label('Nationalité')
                                        ->maxLength(255)
                                        ->default('Camerounaise')
                                        ->visible(fn(Get $get) => !$get('est_personne_morale')),

                                    // Personne morale
                                    Forms\Components\TextInput::make('raison_sociale')
                                        ->label('Raison sociale')
                                        ->required(fn(Get $get) => $get('est_personne_morale'))
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('est_personne_morale')),

                                    Forms\Components\TextInput::make('representant_legal')
                                        ->label('Représentant légal')
                                        ->maxLength(255)
                                        ->visible(fn(Get $get) => $get('est_personne_morale')),

                                    // Contact
                                    Forms\Components\Textarea::make('adresse')
                                        ->label('Adresse')
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

                                    // Avocat
                                    Forms\Components\TextInput::make('avocat_nom')
                                        ->label('Avocat')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('avocat_contact')
                                        ->label('Contact avocat')
                                        ->maxLength(255),
                                ])
                                ->itemLabel(
                                    fn(array $state): ?string =>
                                    $state['est_personne_morale'] ?? false
                                        ? ($state['raison_sociale'] ?? 'Nouvelle partie')
                                        : (($state['nom'] ?? '') . ' ' . ($state['prenom'] ?? '') ?: 'Nouvelle partie')
                                )
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->addActionLabel(fn(Get $get) => $get('type_section') === 'repressive'
                                    ? 'Ajouter un prévenu'
                                    : 'Ajouter un défendeur')
                                ->minItems(1)
                                ->defaultItems(1)
                                ->columnSpanFull()
                                ->columns(2),
                        ]),

                    // ✅ ÉTAPE 4 : OBJET DU DIFFÉREND
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
                                            ? 'Sélectionnez les infractions reprochées'
                                            : 'Sélectionnez ou décrivez la nature du différend')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ✅ ÉTAPE 5 : OBSERVATIONS
                    Forms\Components\Wizard\Step::make('Observations')
                        ->schema([
                            Forms\Components\Textarea::make('observations')
                                ->label('Observations')
                                ->maxLength(65535)
                                ->rows(4)
                                ->columnSpanFull()
                                ->helperText('Remarques complémentaires, notes spéciales, etc.'),
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

                Tables\Columns\TextColumn::make('numero_dossier_personnalise')
                    ->label('N° Ancien')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('demandeurs_liste')
                    ->label('Requérants')
                    ->getStateUsing(function ($record) {
                        if ($record->section?->type === 'repressive') {
                            return 'Ministère Public';
                        }
                        return $record->demandeurs_liste ?: $record->demandeur_nom_complet;
                    })
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('defendeurs_liste')
                    ->label('Parties adverses')
                    ->getStateUsing(fn($record) => $record->defendeurs_liste ?: $record->defendeur_nom_complet ?: '-')
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

                Tables\Columns\TextColumn::make('date_premiere_audience')
                    ->label('1ère audience')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn($state) => $state > 0 ? $state : 'Aucune'),
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

                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'ouvert' => 'Ouvert',
                        'en_instance' => 'En instance',
                        'grosse_delivree' => 'Grosse délivrée',
                        'en_recours' => 'En recours',
                        'clos' => 'Clos',
                    ]),

                Tables\Filters\Filter::make('date_premiere_audience')
                    ->form([
                        Forms\Components\DatePicker::make('audience_du')
                            ->label('Audience du')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('audience_au')
                            ->label('Au')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['audience_du'], fn($q, $date) => $q->whereDate('date_premiere_audience', '>=', $date))
                            ->when($data['audience_au'], fn($q, $date) => $q->whereDate('date_premiere_audience', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['audience_du'] ?? null) {
                            $indicators[] = 'Audience du ' . \Carbon\Carbon::parse($data['audience_du'])->format('d/m/Y');
                        }
                        if ($data['audience_au'] ?? null) {
                            $indicators[] = 'Au ' . \Carbon\Carbon::parse($data['audience_au'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('creer_decision')
                        ->label('Créer une décision')
                        ->icon('heroicon-o-scale')
                        ->color('success')
                        ->visible(fn($record) => in_array($record->statut, ['ouvert', 'en_instance']))
                        ->url(fn($record) => \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('create', ['dossier_id' => $record->id])),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
