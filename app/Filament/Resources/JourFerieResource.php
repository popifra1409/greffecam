<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JourFerieResource\Pages;
use App\Models\JourFerie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JourFerieResource extends Resource
{
    protected static ?string $model = JourFerie::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Jour férié';

    protected static ?string $pluralModelLabel = 'Jours fériés';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Fête du travail'),

                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->native(false),

                        Forms\Components\TextInput::make('annee')
                            ->label('Année')
                            ->required()
                            ->numeric()
                            ->default(now()->year)
                            ->minValue(2020)
                            ->maxValue(2050),

                        Forms\Components\Toggle::make('is_recurrent')
                            ->label('Récurrent chaque année')
                            ->helperText('Ex: 1er Mai, 20 Mai, etc.')
                            ->default(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('annee')
                    ->label('Année')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_recurrent')
                    ->label('Récurrent')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('annee')
                    ->label('Année')
                    ->options(function () {
                        $years = [];
                        for ($i = now()->year - 2; $i <= now()->year + 2; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                Tables\Filters\TernaryFilter::make('is_recurrent')
                    ->label('Récurrent')
                    ->placeholder('Tous')
                    ->trueLabel('Récurrents uniquement')
                    ->falseLabel('Non récurrents'),
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
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJourFeries::route('/'),
            'create' => Pages\CreateJourFerie::route('/create'),
            'edit' => Pages\EditJourFerie::route('/{record}/edit'),
        ];
    }
}
