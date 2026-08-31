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
            Forms\Components\Select::make('role_succession')
                ->label('Rôle successoral')
                ->options([
                    'conjoint' => 'Conjoint',
                    'enfant' => 'Enfant',
                    'autre' => 'Autre',
                ])
                ->helperText('Utilisé pour calculer sa part du solde'),

            Forms\Components\TextInput::make('pourcentage_manuel')
                ->label('Part manuelle (%)')
                ->numeric()
                ->suffix('%')
                ->visible(fn(Get $get) => $get('../../regle_repartition') === 'personnalisee')
                ->helperText('Uniquement si la règle "personnalisée" est choisie'),
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
                Tables\Actions\Action::make('voir_repartition')
                    ->label('📊 Voir répartition')
                    ->color('info')
                    ->modalHeading('Répartition du solde')
                    ->modalContent(fn() => view('sequestres.repartition-modal', [
                        'resultat' => app(\App\Services\RepartitionSuccessionService::class)
                            ->calculerRepartition($this->getOwnerRecord()),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalWidth('2xl'),
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
