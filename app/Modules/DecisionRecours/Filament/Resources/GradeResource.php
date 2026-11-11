<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\GradeResource\Pages;
use App\Models\Grade;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Grade';

    protected static ?string $pluralModelLabel = 'Grades';

    protected static ?int $navigationSort = 50;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static function getViewPermission(): string
    {
        return 'view_grades';
    }
    protected static function getCreatePermission(): string
    {
        return 'manage_grades';
    }
    protected static function getEditPermission(): string
    {
        return 'manage_grades';
    }
    protected static function getDeletePermission(): string
    {
        return 'manage_grades';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du grade')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Identifiant technique, ex: magistrat-1er-grade'),

                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Ex: Magistrat 1er grade, Greffier Principal'),

                        Forms\Components\Select::make('type_grade')
                            ->label('Applicable à')
                            ->options([
                                'juge' => '⚖️ Juges uniquement',
                                'greffier' => '🖋️ Greffiers uniquement',
                                'les_deux' => '👥 Juges et Greffiers',
                            ])
                            ->required()
                            ->default('les_deux')
                            ->native(false),

                        Forms\Components\TextInput::make('ordre')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),

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
                    ->sortable(),

                Tables\Columns\TextColumn::make('type_grade')
                    ->label('Applicable à')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'juge' => 'Juges',
                        'greffier' => 'Greffiers',
                        'les_deux' => 'Juges & Greffiers',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'juge' => 'info',
                        'greffier' => 'warning',
                        'les_deux' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('juges_count')
                    ->label('Juges')
                    ->counts('juges')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('greffiers_count')
                    ->label('Greffiers')
                    ->counts('greffiers')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_grade')
                    ->label('Type')
                    ->options([
                        'juge' => 'Juges',
                        'greffier' => 'Greffiers',
                        'les_deux' => 'Juges & Greffiers',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => static::canEdit($record)),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => static::canDelete($record))
                        ->before(function ($record) {
                            abort_unless(static::canDelete($record), 403);
                        }),

                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button(),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => static::canDeleteAny())
                        ->before(function () {
                            abort_unless(static::canDeleteAny(), 403);
                        }),
                ]),
            ])
            ->defaultSort('ordre');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            'create' => Pages\CreateGrade::route('/create'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
        ];
    }
}
