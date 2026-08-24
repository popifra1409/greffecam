<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PartiesTiercesRelationManager extends RelationManager
{
    protected static string $relationship = 'partiesTierces';

    protected static ?string $title = 'Partie Tierce';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type_partie_tierce')
                ->label('Type')
                ->options([
                    'huissier' => 'Huissier',
                    'avocat' => 'Avocat',
                    'service_public' => 'Service public (ENEO, CAMWATER...)',
                    'autre' => 'Autre',
                ])
                ->default('autre')
                ->required(),

            Forms\Components\TextInput::make('nom_complet')
                ->label('Nom / Raison sociale')
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('telephone')
                ->label('Téléphone')
                ->tel(),

            Forms\Components\TextInput::make('reference')
                ->label('Référence')
                ->placeholder('N° facture, n° dossier, contrat...'),

            Forms\Components\TextInput::make('adresse')
                ->label('Adresse')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_complet')
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom / Raison sociale')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_partie_tierce')
                    ->label('Type')
                    ->options([
                        'huissier' => 'Huissier',
                        'avocat' => 'Avocat',
                        'service_public' => 'Service public',
                        'autre' => 'Autre',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('➕ Ajouter une partie tierce'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->defaultSort('nom_complet')
            ->paginated([10, 25, 50, 'all'])
            ->emptyStateHeading('Aucune partie tierce')
            ->emptyStateDescription('Ajoutez les prestataires (huissier, avocat, services publics...).');
    }
}
