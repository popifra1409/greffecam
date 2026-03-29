<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource\Pages;
use App\Models\AnneeJudiciaire;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AnneeJudiciaireResource extends Resource
{
    protected static ?string $model = AnneeJudiciaire::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Paramétrage';

    protected static ?string $modelLabel = 'Année judiciaire';

    protected static ?string $pluralModelLabel = 'Années judiciaires';

    protected static ?int $navigationSort = 1;

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
                            ->placeholder('Ex: 2024-2025')
                            ->helperText('Format recommandé: YYYY-YYYY'),

                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->month >= 10 ? now()->startOfMonth() : now()->subYear()->month(10)->startOfMonth())
                            ->helperText('Généralement le 1er octobre'),

                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('date_debut')
                            ->default(now()->month >= 10 ? now()->addYear()->month(9)->endOfMonth() : now()->month(9)->endOfMonth())
                            ->helperText('Généralement le 30 septembre'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Année active')
                            ->helperText('Une seule année peut être active à la fois')
                            ->default(false),

                        Forms\Components\Toggle::make('is_cloturee')
                            ->label('Année clôturée')
                            ->helperText('Une année clôturée ne peut plus être modifiée')
                            ->default(false)
                            ->disabled(fn($record) => $record?->is_active),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observations')
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
                    ->label('Année judiciaire')
                    ->searchable()
                    ->sortable()
                    ->size('lg')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_cloturee')
                    ->label('Clôturée')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('decisions_count')
                    ->label('Nb. Décisions')
                    ->counts('decisions')
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
                    ->label('Active')
                    ->placeholder('Toutes')
                    ->trueLabel('Active uniquement')
                    ->falseLabel('Inactives uniquement'),

                Tables\Filters\TernaryFilter::make('is_cloturee')
                    ->label('Clôturée')
                    ->placeholder('Toutes')
                    ->trueLabel('Clôturées uniquement')
                    ->falseLabel('Ouvertes uniquement'),
            ])
            ->actions([
                Tables\Actions\Action::make('activer')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => !$record->is_active && !$record->is_cloturee)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->activer();
                        Notification::make()
                            ->title('Année judiciaire activée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->decisions_count === 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_debut', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnneeJudiciaires::route('/'),
            'create' => Pages\CreateAnneeJudiciaire::route('/create'),
            'edit' => Pages\EditAnneeJudiciaire::route('/{record}/edit'),
        ];
    }
}
