<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\TypeDecisionResource\Pages;
use App\Models\TypeDecision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Traits\HasResourcePermissions;

class TypeDecisionResource extends Resource
{
    use HasResourcePermissions;

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

    protected static ?string $model = TypeDecision::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Type de décision';

    protected static ?string $pluralModelLabel = 'Types de décisions';

    protected static ?int $navigationSort = 50;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Select::make('categorie_decision_id')
                            ->label('Catégorie de décision')
                            ->relationship('categorieDecision', 'libelle')
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
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2),
                            ]),

                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Jugement au fond, Ordonnance de référé'),

                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ex: JUG_FOND, ORD_REF'),

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
                Tables\Columns\TextColumn::make('categorieDecision.libelle')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

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
                Tables\Filters\SelectFilter::make('categorie_decision_id')
                    ->label('Catégorie')
                    ->relationship('categorieDecision', 'libelle'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
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
            ->defaultSort('categorieDecision.libelle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeDecisions::route('/'),
            'create' => Pages\CreateTypeDecision::route('/create'),
            'edit' => Pages\EditTypeDecision::route('/{record}/edit'),
        ];
    }
}
