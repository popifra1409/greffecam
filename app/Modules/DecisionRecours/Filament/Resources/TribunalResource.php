<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\TribunalResource\Pages;
use App\Models\Tribunal;
use App\Traits\HasResourcePermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TribunalResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Tribunal::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Tribunal';

    protected static ?string $pluralModelLabel = 'Tribunaux';

    protected static ?int $navigationSort = 10;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static function getViewPermission(): string
    {
        return 'view_referentiels';
    }

    protected static function getCreatePermission(): string
    {
        return 'manage_referentiels';
    }

    protected static function getEditPermission(): string
    {
        return 'manage_referentiels';
    }

    protected static function getDeletePermission(): string
    {
        return 'manage_referentiels';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du tribunal')
                    ->description('Le sigle est utilisé dans la génération automatique des numéros de dossier')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom du tribunal')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Tribunal de Première Instance de Yaoundé')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('sigle')
                            ->label('Sigle / Abréviation')
                            ->required() // ✅ OBLIGATOIRE
                            ->unique(ignoreRecord: true) // ✅ UNIQUE
                            ->maxLength(50)
                            ->placeholder('Ex: TPI-CA')
                            ->helperText('⚠️ Obligatoire - Utilisé dans la nomenclature des dossiers (ex: TPI-CA/TPD)')
                            ->rule('alpha_dash') // Seulement lettres, chiffres, tirets et underscores
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('ville')
                            ->label('Ville')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('adresse')
                            ->label('Adresse complète')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('telephone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sigle')
                    ->label('Sigle')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->size('lg')
                    ->weight('bold')
                    ->copyable()
                    ->tooltip('Utilisé dans la nomenclature des dossiers'),

                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn($record) => $record->ville),

                Tables\Columns\TextColumn::make('ville')
                    ->label('Ville')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dossiers_count')
                    ->label('Nb. Dossiers')
                    ->counts('dossiers')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('decisions_count')
                    ->label('Nb. Décisions')
                    ->counts('decisions')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => static::canEdit($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => static::canDelete($record)
                        && $record->dossiers_count == 0
                        && $record->decisions_count == 0)
                    ->before(function ($record) {
                        abort_unless(static::canDelete($record), 403);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => static::canDeleteAny())
                        ->before(function () {
                            abort_unless(static::canDeleteAny(), 403);
                        }),
                ]),
            ])
            ->defaultSort('sigle');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nom', 'sigle', 'ville'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->nom;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Sigle' => $record->sigle,
            'Ville' => $record->ville,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTribunals::route('/'),
            'create' => Pages\CreateTribunal::route('/create'),
            'edit' => Pages\EditTribunal::route('/{record}/edit'),
        ];
    }
}