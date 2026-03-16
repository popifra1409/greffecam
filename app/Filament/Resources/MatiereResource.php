<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatiereResource\Pages;
use App\Models\Matiere;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MatiereResource extends Resource
{
    protected static ?string $model = Matiere::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Matière';

    protected static ?string $pluralModelLabel = 'Matières';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Select::make('section_id')
                            ->label('Section')
                            ->relationship('section', 'libelle')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('libelle')
                                    ->label('Libellé')
                                    ->required(),
                                Forms\Components\TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(20),
                            ]),

                        Forms\Components\TextInput::make('designation')
                            ->label('Désignation')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Droit du travail, Accidents de circulation'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.libelle')
                    ->label('Section')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('decisions_count')
                    ->label('Nb. Décisions')
                    ->counts('decisions')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('Section')
                    ->relationship('section', 'libelle'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
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
            ->defaultSort('section.libelle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMatieres::route('/'),
            'create' => Pages\CreateMatiere::route('/create'),
            'edit' => Pages\EditMatiere::route('/{record}/edit'),
        ];
    }
}
