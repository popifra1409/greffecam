<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\NatureDecisionResource\Pages;
use App\Models\NatureDecision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Traits\HasResourcePermissions;

class NatureDecisionResource extends Resource
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

    protected static ?string $model = NatureDecision::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Nature de décision';

    protected static ?string $pluralModelLabel = 'Natures de décisions';

    protected static ?int $navigationSort = 40;

    protected static bool $hasTitleCaseModelLabel = false;

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
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->helperText('Code unique (ex: JUG_CORR, ORD_NONLIEU)'),

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
                    ->badge(),

                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNatureDecisions::route('/'),
            'create' => Pages\CreateNatureDecision::route('/create'),
            'edit' => Pages\EditNatureDecision::route('/{record}/edit'),
        ];
    }
}
