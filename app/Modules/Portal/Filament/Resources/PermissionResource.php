<?php

namespace App\Modules\Portal\Filament\Resources;

use App\Modules\Portal\Filament\Resources\PermissionResource\Pages;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'Permissions';

    protected static ?int $navigationSort = 3;

    protected static function getViewPermission(): string
    {
        return 'view_permissions';
    }
    protected static function getCreatePermission(): string
    {
        return 'manage_permissions';
    }
    protected static function getEditPermission(): string
    {
        return 'manage_permissions';
    }
    protected static function getDeletePermission(): string
    {
        return 'manage_permissions';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la permission')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la permission')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['regex:/^[a-z_]+$/'])
                            ->validationMessages([
                                'regex' => 'Utilisez uniquement des minuscules et underscores (ex: view_decisions)',
                            ])
                            ->helperText('Ex: view_decisions, create_recours, delete_users'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Nb. Rôles')
                    ->counts('roles')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
