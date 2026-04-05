<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;
use App\Models\CollegeJuge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CollegeJugeResource extends Resource
{
    protected static ?string $model = CollegeJuge::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Collège de juges';

    protected static ?string $pluralModelLabel = 'Collégialité';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du collège')
                    ->schema([
                        Forms\Components\Select::make('tribunal_id')
                            ->label('Tribunal')
                            ->relationship('tribunal', 'nom')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),

                        Forms\Components\TextInput::make('designation')
                            ->label('Désignation')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Collège correctionnel 1'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Composition du collège')
                    ->schema([
                        Forms\Components\Repeater::make('membres') // ✅ Changé de 'juges' à 'membres'
                            ->schema([
                                Forms\Components\Select::make('juge_id')
                                    ->label('Juge')
                                    ->options(function (callable $get) {
                                        $tribunalId = $get('../../tribunal_id');
                                        if ($tribunalId) {
                                            return \App\Models\Juge::where('tribunal_id', $tribunalId)
                                                ->where('is_active', true)
                                                ->get()
                                                ->pluck('nom_complet', 'id');
                                        }
                                        return [];
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disableOptionWhen(function ($value, $state, callable $get) {
                                        // Empêcher de sélectionner le même juge plusieurs fois
                                        $selectedJuges = collect($get('../../membres'))
                                            ->pluck('juge_id')
                                            ->filter()
                                            ->toArray();
                                        return in_array($value, $selectedJuges) && $value != $state;
                                    }),

                                Forms\Components\Select::make('qualite')
                                    ->label('Qualité')
                                    ->options([
                                        'president' => 'Président',
                                        'membre' => 'Membre',
                                        'assesseur' => 'Assesseur',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->addActionLabel('➕ Ajouter un juge')
                            ->columnSpanFull()
                            ->defaultItems(1)
                            // ->minItems(3)
                            // ->maxItems(7)
                            ->reorderable()
                        // ->helperText('Un collège doit comporter au minimum 3 juges (1 président + 2 membres/assesseurs)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tribunal.nom')
                    ->label('Tribunal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('juges_count')
                    ->label('Nb. Juges')
                    ->counts('juges')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tribunal_id')
                    ->label('Tribunal')
                    ->relationship('tribunal', 'nom'),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollegeJuges::route('/'),
            'create' => Pages\CreateCollegeJuge::route('/create'),
            'edit' => Pages\EditCollegeJuge::route('/{record}/edit'),
            'view' => Pages\ViewCollegeJuge::route('/{record}'),
        ];
    }
}
