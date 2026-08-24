<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PartiesAdversesRelationManager extends RelationManager
{
    protected static string $relationship = 'partiesAdverses';

    protected static ?string $title = 'Parties adverses';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nom_complet')->label('Nom complet')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('numero_cni')->label('N° CNI'),
            Forms\Components\TextInput::make('telephone')->label('Téléphone')->tel(),
            Forms\Components\TextInput::make('adresse')->label('Adresse')->columnSpanFull(),

            Forms\Components\DatePicker::make('date_debut_paiement')
                ->label('Date de début des paiements')
                ->native(false)
                ->displayFormat('d/m/Y'),

            Forms\Components\TextInput::make('montant_echeance')
                ->label('Montant par échéance')
                ->numeric()
                ->suffix('FCFA'),

            Forms\Components\Select::make('periodicite')
                ->label('Périodicité')
                ->options([
                    'mensuel' => 'Mensuel',
                    'trimestriel' => 'Trimestriel',
                    'semestriel' => 'Semestriel',
                    'annuel' => 'Annuel',
                ])
                ->default('mensuel')
                ->native(false),

            Forms\Components\TextInput::make('duree_contrat_mois')
                ->label('Durée du contrat (mois)')
                ->numeric()
                ->suffix('mois')
                ->placeholder('Indéterminée'),
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

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('montant_echeance')
                    ->label('Montant/échéance')
                    ->money('XAF')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('periodicite_label')
                    ->label('Périodicité')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('statut_versement_label')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statut_versement_couleur),

                Tables\Columns\TextColumn::make('reste_a_payer')
                    ->label('Reste à payer')
                    ->money('XAF')
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('➕ Ajouter une partie adverse'),
            ])
            ->actions([
                Tables\Actions\Action::make('voir_echeancier')
                    ->label('Échéancier')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->visible(fn($record) => filled($record->date_debut_paiement) && filled($record->montant_echeance))
                    ->modalHeading(fn($record) => "Échéancier — {$record->nom_complet}")
                    ->modalContent(fn($record) => view('sequestres.echeancier-modal', ['partieAdverse' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalWidth('2xl'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->defaultSort('nom_complet')
            ->paginated([10, 25, 50, 'all'])
            ->emptyStateHeading('Aucune partie adverse')
            ->emptyStateDescription('Ajoutez les locataires ou payeurs de ce séquestre.');
    }
}
