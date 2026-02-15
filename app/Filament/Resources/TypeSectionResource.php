<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeSectionResource\Pages;
use App\Models\TypeSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TypeSectionResource extends Resource
{
    protected static ?string $model = TypeSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Type de section';

    protected static ?string $pluralModelLabel = 'Types de sections';

    protected static ?int $navigationSort = 2;

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
                            ->placeholder('Ex: Pénal, Foncier'),

                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ex: PEN, FONC'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('utilise_assesseur')
                            ->label('Utilise des assesseurs')
                            ->helperText('Cochez si cette section utilise des assesseurs au lieu de juges')
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Types de parties')
                    ->schema([
                        Forms\Components\KeyValue::make('types_parties')
                            ->label('Types de parties autorisés')
                            ->keyLabel('Code')
                            ->valueLabel('Libellé')
                            ->helperText('Définissez les types de parties pour ce type de section (Ex: demandeur => Demandeur, defendeur => Défendeur)')
                            ->reorderable()
                            ->addActionLabel('Ajouter un type de partie')
                            ->default([
                                'demandeur' => 'Demandeur',
                                'defendeur' => 'Défendeur',
                                'temoin' => 'Témoin',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('utilise_assesseur')
                    ->label('Assesseurs')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sections_count')
                    ->label('Nb. Sections')
                    ->counts('sections')
                    ->badge()
                    ->color('info')
                    ->sortable(),

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->sections_count === 0),
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
            'index' => Pages\ListTypeSections::route('/'),
            'create' => Pages\CreateTypeSection::route('/create'),
            'edit' => Pages\EditTypeSection::route('/{record}/edit'),
        ];
    }
}
