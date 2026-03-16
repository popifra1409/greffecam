<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GreffierResource\Pages;
use App\Models\Greffier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GreffierResource extends Resource
{
    protected static ?string $model = Greffier::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Greffier';

    protected static ?string $pluralModelLabel = 'Greffiers';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du greffier')
                    ->schema([
                        Forms\Components\Select::make('tribunal_id')
                            ->label('Tribunal')
                            ->relationship('tribunal', 'nom')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),

                        Forms\Components\TextInput::make('matricule')
                            ->label('Matricule')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('titre')
                            ->label('Titre')
                            ->options([
                                'M.' => 'Monsieur',
                                'Mme' => 'Madame',
                            ])
                            ->default('M.'),

                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('prenom')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('grade')
                            ->label('Grade')
                            ->options([
                                'Greffier en Chef' => 'Greffier en Chef',
                                'Greffier Principal' => 'Greffier Principal',
                                'Greffier' => 'Greffier',
                                'Greffier Adjoint' => 'Greffier Adjoint',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('telephone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('est_chef')
                            ->label('Greffier en Chef')
                            ->helperText('Cochez si ce greffier est le greffier en chef du tribunal')
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Affectations')
                    ->schema([
                        Forms\Components\Select::make('sections')
                            ->label('Sections affectées')
                            ->relationship('sections', 'libelle', function ($query, callable $get) {
                                $tribunalId = $get('tribunal_id');
                                if ($tribunalId) {
                                    // Filtrer les sections par le tribunal sélectionné
                                    // Note: Si vos sections sont globales, retirez ce filtre
                                    return $query->where('is_active', true);
                                }
                                return $query->where('is_active', true);
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Sélectionnez les sections auxquelles ce greffier est affecté')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->getStateUsing(fn($record) => $record->nom_complet)
                    ->searchable(['nom', 'prenom'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('est_chef')
                    ->label('Chef')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('tribunal.nom')
                    ->label('Tribunal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sections_count')
                    ->label('Sections')
                    ->counts('sections')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tribunal_id')
                    ->label('Tribunal')
                    ->relationship('tribunal', 'nom'),

                Tables\Filters\TernaryFilter::make('est_chef')
                    ->label('Greffier en Chef')
                    ->placeholder('Tous')
                    ->trueLabel('Greffiers en Chef uniquement')
                    ->falseLabel('Greffiers ordinaires'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
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
            ->defaultSort('nom');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGreffiers::route('/'),
            'create' => Pages\CreateGreffier::route('/create'),
            'edit' => Pages\EditGreffier::route('/{record}/edit'),
            'view' => Pages\ViewGreffier::route('/{record}'),
        ];
    }
}
