<?php

namespace App\Modules\Portal\Filament\Resources;

use App\Modules\Portal\Filament\Resources\UserResource\Pages;
use App\Models\Greffier;
use App\Models\User;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?int $navigationSort = 10;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static function getViewPermission(): string
    {
        return 'view_users';
    }
    protected static function getCreatePermission(): string
    {
        return 'create_users';
    }
    protected static function getEditPermission(): string
    {
        return 'edit_users';
    }
    protected static function getDeletePermission(): string
    {
        return 'delete_users';
    }

    /**
     * ✅ Protection supplémentaire : empêcher la suppression/désactivation
     * du compte Super Administrateur unique.
     */
    protected static function estCompteSuperAdminUnique($record): bool
    {
        return $record?->hasRole('Super Administrateur') ?? false;
    }

    /**
     * Détecte, dans l'état du formulaire, la sélection d'un rôle greffier.
     */
    protected static function roleGreffierSelectionne(Get $get, array $nomsRoles = ['Greffier']): bool
    {
        $roleIds = Role::whereIn('name', $nomsRoles)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if ($roleIds === []) {
            return false;
        }

        $selection = array_filter(
            (array) $get('roles'),
            fn ($valeur) => is_scalar($valeur) || $valeur instanceof \Illuminate\Database\Eloquent\Model
        );

        $selection = array_map(
            fn ($valeur) => (string) ($valeur instanceof \Illuminate\Database\Eloquent\Model ? $valeur->getKey() : $valeur),
            $selection
        );

        return array_intersect($roleIds, $selection) !== [];
    }

    public static function canDelete($record): bool
    {
        if (static::estCompteSuperAdminUnique($record)) {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function canDeleteAny(): bool
    {
        return parent::canDeleteAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->required(),

                        Forms\Components\Select::make('roles')
                            ->label('Rôles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->live()
                            ->required()
                            // ✅ Empêche d'attribuer le rôle Super Administrateur
                            // depuis ce formulaire (un seul compte doit le porter,
                            // géré exclusivement par le seeder).
                            ->options(fn() => \Spatie\Permission\Models\Role::where('name', '!=', 'Super Administrateur')
                                ->pluck('name', 'id'))
                            ->helperText('Le rôle "Super Administrateur" ne peut pas être attribué depuis cette interface.'),
                    ])->columns(2),

                Forms\Components\Section::make('Rattachement greffier')
                    ->description('Pour un compte greffier, le greffier rattaché détermine la ou les sections dont les dossiers sont visibles.')
                    ->schema([
                        Forms\Components\Select::make('greffier_id')
                            ->label('Greffier rattaché')
                            ->options(fn() => Greffier::actif()
                                ->with('tribunal')
                                ->orderBy('nom')
                                ->get()
                                ->mapWithKeys(fn(Greffier $greffier) => [
                                    $greffier->id => trim(
                                        ($greffier->matricule ? $greffier->matricule . ' — ' : '')
                                        . $greffier->nom_complet
                                        . ' (' . ($greffier->tribunal?->nom ?? 'sans tribunal') . ')'
                                    ),
                                ]))
                            ->searchable()
                            ->live()
                            ->required(fn(Get $get) => static::roleGreffierSelectionne($get))
                            ->helperText('Les sections sont affectées sur la fiche du greffier (Paramétrage > Greffiers).')
                            ->columnSpanFull(),

                        // ✅ Miroir en lecture seule des colonnes « Matricule greffier »
                        // et « Section(s) » de la liste : la donnée se modifie uniquement
                        // sur la fiche du greffier, jamais en double saisie ici.
                        Forms\Components\Placeholder::make('matricule_greffier')
                            ->label('Matricule greffier')
                            ->content(function (Get $get): HtmlString|string {
                                $matricule = Greffier::find($get('greffier_id'))?->matricule;

                                if (blank($matricule)) {
                                    return '—';
                                }

                                return new HtmlString(
                                    '<span style="display:inline-block;padding:2px 8px;border-radius:6px;background:#f3f4f6;color:#374151;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;font-weight:600;">'
                                    . e($matricule)
                                    . '</span>'
                                );
                            }),

                        Forms\Components\Placeholder::make('sections_greffier')
                            ->label('Section(s)')
                            ->content(function (Get $get): HtmlString|string {
                                $greffier = Greffier::with('sections')->find($get('greffier_id'));

                                if (! $greffier) {
                                    return '—';
                                }

                                if ($greffier->sections->isEmpty()) {
                                    return new HtmlString(
                                        '<span style="display:inline-block;padding:2px 8px;border-radius:6px;background:#fef3c7;color:#92400e;font-size:12px;font-weight:600;">'
                                        . '⚠️ Aucune section affectée — ce greffier ne verra que les dossiers qu\'il a enrôlés ou qui lui sont affectés.'
                                        . '</span>'
                                    );
                                }

                                return new HtmlString(
                                    $greffier->sections
                                        ->map(fn($section) => '<span style="display:inline-block;padding:2px 8px;border-radius:9999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:600;margin:0 4px 4px 0;">'
                                            . e($section->libelle)
                                            . '</span>')
                                        ->join('')
                                );
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn(Get $get) => static::roleGreffierSelectionne($get, ['Greffier', 'Greffier en Chef'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with('greffier.sections'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('greffier.matricule')
                    ->label('Matricule greffier')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sections_greffier')
                    ->label('Section(s)')
                    ->getStateUsing(fn(User $record) => $record->greffier?->sections->pluck('libelle')->all() ?? [])
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name')
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => !static::estCompteSuperAdminUnique($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
            'Rôles' => $record->roles->pluck('name')->join(', ') ?: 'Aucun rôle',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
