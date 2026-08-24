<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AyantsDroitRelationManager extends RelationManager
{
    protected static string $relationship = 'ayantsDroit';

    protected static ?string $title = 'Ayants droit';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nom_complet')->label('Nom complet')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('numero_cni')->label('N° CNI'),
            Forms\Components\TextInput::make('telephone')->label('Téléphone')->tel(),
            Forms\Components\TextInput::make('adresse')->label('Adresse')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_complet')
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('numero_cni')
                    ->label('N° CNI')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                Tables\Columns\TextColumn::make('adresse')
                    ->label('Adresse')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('➕ Ajouter un ayant droit'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->defaultSort('nom_complet')
            ->paginated([10, 25, 50, 'all'])
            ->emptyStateHeading('Aucun ayant droit')
            ->emptyStateDescription('Ajoutez les bénéficiaires légaux de ce séquestre.');
    }
}
