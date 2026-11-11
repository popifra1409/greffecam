<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use App\Traits\HasResourcePermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Section';

    protected static ?string $pluralModelLabel = 'Sections';

    protected static ?int $navigationSort = 20;

    protected static bool $hasTitleCaseModelLabel = false;

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
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Civil, Commercial, Correctionnel'),

                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ex: CIV, COMM, CORR'),

                        Forms\Components\Select::make('type')
                            ->label('Type de section')
                            ->options([
                                'repressive' => 'Section Répressive',
                                'non_repressive' => 'Section Non Répressive',
                            ])
                            ->required()
                            ->default('non_repressive')
                            ->helperText('Répressive : Correctionnel, Simple Police. Non Répressive : Civil, Commercial, Social, etc.')
                            ->live(),

                        Forms\Components\Placeholder::make('types_parties_info')
                            ->label('Types de parties')
                            ->content(function (callable $get) {
                                $type = $get('type');
                                if ($type === 'repressive') {
                                    return '• Ministère Public • Partie Civile • Prévenu • Témoin';
                                }
                                return '• Demandeur • Défendeur • Témoin';
                            })
                            ->helperText('Les types de parties sont déterminés automatiquement selon le type de section'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('utilise_assesseur')
                            ->label('Utilise des assesseurs')
                            ->helperText('Cochez si cette section utilise des assesseurs au lieu de juges')
                            ->default(false),

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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'repressive' => 'danger',
                        'non_repressive' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'repressive' => 'Répressive',
                        'non_repressive' => 'Non Répressive',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('utilise_assesseur')
                    ->label('Assesseurs')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('matieres_count')
                    ->label('Matières')
                    ->counts('matieres')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('dossiers_count')
                    ->label('Dossiers')
                    ->counts('dossiers')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'repressive' => 'Répressive',
                        'non_repressive' => 'Non Répressive',
                    ]),

                Tables\Filters\TernaryFilter::make('utilise_assesseur')
                    ->label('Utilise assesseurs')
                    ->placeholder('Tous')
                    ->trueLabel('Avec assesseurs')
                    ->falseLabel('Sans assesseurs'),

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
            ->defaultSort('code');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['libelle', 'code'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->libelle;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Code' => $record->code,
            'Type' => $record->estRepressive() ? 'Section répressive' : 'Section non répressive',
        ];
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
