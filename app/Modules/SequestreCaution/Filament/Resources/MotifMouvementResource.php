<?php

namespace App\Modules\SequestreCaution\Filament\Resources;

use App\Modules\SequestreCaution\Filament\Resources\MotifMouvementResource\Pages;
use App\Models\MotifMouvement;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MotifMouvementResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = MotifMouvement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Référentiels';

    protected static ?string $modelLabel = 'Motif de mouvement';

    protected static ?string $pluralModelLabel = 'Motifs de mouvement';

    protected static ?int $navigationSort = 3;

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
                Forms\Components\Section::make('Informations du motif')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Ex: loyer, remboursement, avance'),

                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type_mouvement')
                            ->label('Applicable à')
                            ->options([
                                'versement' => '⬇️ Versement uniquement',
                                'retrait' => '⬆️ Retrait uniquement',
                                'les_deux' => '↕️ Versement et Retrait',
                            ])
                            ->required()
                            ->default('les_deux')
                            ->native(false),

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
                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('type_mouvement')
                    ->label('Applicable à')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'versement' => 'Versement',
                        'retrait' => 'Retrait',
                        'les_deux' => 'Versement & Retrait',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'versement' => 'success',
                        'retrait' => 'danger',
                        'les_deux' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('mouvements_count')
                    ->label('Utilisations')
                    ->counts('mouvements')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_mouvement')
                    ->label('Type')
                    ->options([
                        'versement' => 'Versement',
                        'retrait' => 'Retrait',
                        'les_deux' => 'Versement & Retrait',
                    ]),
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
            'index' => Pages\ListMotifMouvements::route('/'),
            'create' => Pages\CreateMotifMouvement::route('/create'),
            'edit' => Pages\EditMotifMouvement::route('/{record}/edit'),
        ];
    }
}
