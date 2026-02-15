<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlerteRecoursResource\Pages;
use App\Models\AlerteRecours;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlerteRecoursResource extends Resource
{
    protected static ?string $model = AlerteRecours::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Alerte';

    protected static ?string $pluralModelLabel = 'Alertes Délais';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('est_lue', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('est_lue', false)->count();

        if ($count > 10) {
            return 'danger';
        } elseif ($count > 5) {
            return 'warning';
        }

        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de l\'alerte')
                    ->schema([
                        Forms\Components\Select::make('niveau')
                            ->label('Niveau')
                            ->options([
                                'rouge' => 'Urgent (H-48)',
                                'orange' => 'Attention (J-7)',
                                'jaune' => 'Info (J-15)',
                            ])
                            ->disabled(),

                        Forms\Components\Select::make('recours_id')
                            ->label('Recours')
                            ->relationship('recours', 'numero_recours')
                            ->disabled(),

                        Forms\Components\TextInput::make('titre')
                            ->label('Titre')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('date_declenchement')
                            ->label('Date de déclenchement')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('date_lecture')
                            ->label('Date de lecture')
                            ->disabled(),

                        Forms\Components\Toggle::make('est_lue')
                            ->label('Marquée comme lue')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('niveau')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'rouge' => 'danger',
                        'orange' => 'warning',
                        'jaune' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'rouge' => 'URGENT',
                        'orange' => 'ATTENTION',
                        'jaune' => 'INFO',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('recours.numero_recours')
                    ->label('Recours')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.recours.view', $record->recours_id))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),

                Tables\Columns\IconColumn::make('est_lue')
                    ->label('Lue')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_declenchement')
                    ->label('Déclenchée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_lecture')
                    ->label('Lue le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non lue')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('niveau')
                    ->label('Niveau')
                    ->options([
                        'rouge' => 'Urgent',
                        'orange' => 'Attention',
                        'jaune' => 'Info',
                    ]),

                Tables\Filters\TernaryFilter::make('est_lue')
                    ->label('Statut')
                    ->placeholder('Toutes')
                    ->trueLabel('Lues')
                    ->falseLabel('Non lues'),
            ])
            ->actions([
                Tables\Actions\Action::make('marquer_lue')
                    ->label('Marquer comme lue')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn($record) => !$record->est_lue)
                    ->action(function ($record) {
                        $record->update([
                            'est_lue' => true,
                            'date_lecture' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('marquer_lues')
                    ->label('Marquer comme lues')
                    ->icon('heroicon-o-check')
                    ->action(function ($records) {
                        $records->each->update([
                            'est_lue' => true,
                            'date_lecture' => now(),
                        ]);
                    }),
            ])
            ->defaultSort('date_declenchement', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlerteRecours::route('/'),
        ];
    }
}
