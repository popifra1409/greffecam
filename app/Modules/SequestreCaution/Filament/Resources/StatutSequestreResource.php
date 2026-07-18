<?php

namespace App\Modules\SequestreCaution\Filament\Resources;

use App\Modules\SequestreCaution\Filament\Resources\StatutSequestreResource\Pages;
use App\Models\StatutSequestre;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatutSequestreResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = StatutSequestre::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Statut de séquestre';

    protected static ?string $pluralModelLabel = 'Statuts de séquestre';

    protected static ?int $navigationSort = 2;

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
                Forms\Components\Section::make('Informations du statut')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Ex: ouvert, cloture, appel'),

                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('couleur')
                            ->label('Couleur du badge')
                            ->options([
                                'success' => '🟢 Vert (succès)',
                                'warning' => '🟠 Orange (attention)',
                                'danger' => '🔴 Rouge (danger)',
                                'info' => '🔵 Bleu (info)',
                                'gray' => '⚪ Gris (neutre)',
                            ])
                            ->required()
                            ->default('gray')
                            ->native(false),

                        Forms\Components\TextInput::make('ordre')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Détermine l\'ordre dans les listes déroulantes'),

                        Forms\Components\Toggle::make('bloque_mouvements')
                            ->label('Bloque les nouveaux mouvements')
                            ->helperText('Si activé, aucun versement/retrait ne pourra être ajouté sur un séquestre ayant ce statut (ex: Clôturé)')
                            ->default(false)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ordre')
                    ->label('#')
                    ->sortable()
                    ->width('40px'),

                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->couleur),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('bloque_mouvements')
                    ->label('Bloque mouvements')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('sequestres_count')
                    ->label('Séquestres')
                    ->counts('sequestres')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ordre');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatutSequestres::route('/'),
            'create' => Pages\CreateStatutSequestre::route('/create'),
            'edit' => Pages\EditStatutSequestre::route('/{record}/edit'),
        ];
    }
}
