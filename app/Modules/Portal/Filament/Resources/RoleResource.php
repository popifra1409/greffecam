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

    protected static ?int $navigationSort = 2;

    protected static function getViewPermission(): string { return 'view_roles'; }
    protected static function getCreatePermission(): string { return 'manage_roles'; }
    protected static function getEditPermission(): string { return 'manage_roles'; }
    protected static function getDeletePermission(): string { return 'manage_roles'; }

    public static function estRoleProtege($record): bool
    {
        return $record?->name === 'Super Administrateur';
    }

    public static function canEdit($record): bool
    {
        if (static::estRoleProtege($record)) {
            return false;
        }
        return parent::canEdit($record);
    }

    public static function canDelete($record): bool
    {
        if (static::estRoleProtege($record)) {
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
                            ->disabled(fn($record) => $record && static::estRoleProtege($record)),

                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->relationship('permissions', 'name')
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Sélectionnez les permissions associées à ce rôle')
                            ->columnSpanFull(),
                    ]),
            ]);
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
                    ->color(fn($record) => static::estRoleProtege($record) ? 'danger' : 'primary'),

                Tables\Columns\IconColumn::make('protege')
                    ->label('')
                    ->getStateUsing(fn($record) => static::estRoleProtege($record))
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
                    ->visible(fn($record) => !static::estRoleProtege($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => !static::estRoleProtege($record)),
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
