<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DossierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DecisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'decisions';

    protected static ?string $title = 'Décisions rendues';

    protected static ?string $recordTitleAttribute = 'numero_rg';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_rg')
            ->columns([
                Tables\Columns\TextColumn::make('numero_rg')
                    ->label('N° RG')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('numero_repertoire')
                    ->label('N° Décision')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('date_decision')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('natureDecision.libelle')
                    ->label('Nature')
                    ->badge()
                    ->wrap(),

                Tables\Columns\TextColumn::make('composition')
                    ->label('Composition')
                    ->getStateUsing(fn($record) => $record->composition)
                    ->wrap(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'brouillon' => 'gray',
                        'transmise_chef' => 'warning',
                        'signee' => 'info',
                        'enregistree' => 'success',
                        'annulee' => 'danger',
                        'archivee' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'brouillon' => 'Brouillon',
                        'transmise_chef' => 'Transmise',
                        'signee' => 'Signée',
                        'enregistree' => 'Enregistrée',
                        'annulee' => 'Annulée',
                        'archivee' => 'Archivée',
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nouvelle décision')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data) {
                        // Pré-remplir avec les infos du dossier
                        $dossier = $this->getOwnerRecord();

                        $data['tribunal_id'] = $dossier->tribunal_id;
                        $data['section_id'] = $dossier->section_id;
                        $data['matiere_id'] = $dossier->matiere_id;
                        $data['annee_judiciaire_id'] = $dossier->annee_judiciaire_id;
                        $data['greffier_responsable_id'] = auth()->id();
                        $data['detenteur_actuel_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
