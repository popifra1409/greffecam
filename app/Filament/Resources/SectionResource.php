<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Section';

    protected static ?string $pluralModelLabel = 'Sections';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Select::make('tribunal_id')
                            ->label('Tribunal')
                            ->relationship('tribunal', 'nom')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('nom')
                            ->label('Nom de la section')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Section Civile'),

                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ex: CIV, CORR'),

                        Forms\Components\Select::make('type')
                            ->label('Type de section')
                            ->options([
                                'civil' => 'Civil',
                                'commercial' => 'Commercial',
                                'social' => 'Social',
                                'correctionnel' => 'Correctionnel',
                                'tdl' => 'Tribunal de Droit Local (TDL)',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
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
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'civil' => 'info',
                        'commercial' => 'success',
                        'social' => 'warning',
                        'correctionnel' => 'danger',
                        'tdl' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'civil' => 'Civil',
                        'commercial' => 'Commercial',
                        'social' => 'Social',
                        'correctionnel' => 'Correctionnel',
                        'tdl' => 'TDL',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('tribunal.ville')
                    ->label('Tribunal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('decisions_count')
                    ->label('Décisions')
                    ->counts('decisions')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'civil' => 'Civil',
                        'commercial' => 'Commercial',
                        'social' => 'Social',
                        'correctionnel' => 'Correctionnel',
                        'tdl' => 'TDL',
                    ]),

                Tables\Filters\SelectFilter::make('tribunal_id')
                    ->label('Tribunal')
                    ->relationship('tribunal', 'nom'),
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
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
