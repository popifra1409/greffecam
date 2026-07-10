<?php

namespace App\Modules\Portal\Filament\Resources;

use App\Modules\Portal\Filament\Resources\RoleResource\Pages;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'Rôle';

    protected static ?string $pluralModelLabel = 'Rôles';

    protected static ?int $navigationSort = 90;

    // ✅ Permissions requises pour ce Resource
    protected static function getViewPermission(): string
    {
        return 'view_roles';
    }

    protected static function getCreatePermission(): string
    {
        return 'manage_roles';
    }

    protected static function getEditPermission(): string
    {
        return 'manage_roles';
    }

    protected static function getDeletePermission(): string
    {
        return 'manage_roles';
    }

    // ✅ Rôles système protégés contre modification/suppression
    protected static function getRolesProtegees(): array
    {
        return ['Super Administrateur'];
    }

    public static function canEdit($record): bool
    {
        if (in_array($record->name, static::getRolesProtegees())) {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete($record): bool
    {
        if (in_array($record->name, static::getRolesProtegees())) {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du rôle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du rôle')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn($record) => $record && in_array($record->name, static::getRolesProtegees())),

                        // ✅ Permissions groupées par module pour lisibilité
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->relationship('permissions', 'name')
                            ->options(function () {
                                return \Spatie\Permission\Models\Permission::all()
                                    ->pluck('name', 'name')
                                    ->toArray();
                            })
                            ->descriptions(fn() => static::getPermissionsDescriptions())
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Sélectionnez les permissions associées à ce rôle')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Descriptions lisibles pour chaque permission (aide à la compréhension)
     */
    protected static function getPermissionsDescriptions(): array
    {
        return [
            'view_dossiers' => 'Consulter les dossiers',
            'create_dossiers' => 'Créer des dossiers',
            'edit_dossiers' => 'Modifier des dossiers',
            'delete_dossiers' => 'Supprimer des dossiers',
            'view_decisions' => 'Consulter les décisions',
            'create_decisions' => 'Créer des décisions',
            'edit_decisions' => 'Modifier des décisions',
            'validate_decisions' => 'Valider des décisions',
            'sign_decisions' => 'Signer des décisions',
            'view_recours' => 'Consulter les recours',
            'create_recours' => 'Déclarer des recours',
            'edit_recours' => 'Modifier des recours',
            'view_users' => 'Consulter les utilisateurs',
            'manage_roles' => 'Gérer les rôles et permissions',
            'access_decision_recours' => 'Accéder au module Décisions & Recours',
            'access_administration' => 'Accéder à l\'administration',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du rôle')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => in_array($record->name, static::getRolesProtegees()) ? 'danger' : 'primary'),

                Tables\Columns\IconColumn::make('protege')
                    ->label('')
                    ->getStateUsing(fn($record) => in_array($record->name, static::getRolesProtegees()))
                    ->icon(fn($state) => $state ? 'heroicon-o-lock-closed' : '')
                    ->color('danger')
                    ->tooltip('Rôle système protégé'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Nb. Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Nb. Utilisateurs')
                    ->counts('users')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => !in_array($record->name, static::getRolesProtegees())),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => !in_array($record->name, static::getRolesProtegees())),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
