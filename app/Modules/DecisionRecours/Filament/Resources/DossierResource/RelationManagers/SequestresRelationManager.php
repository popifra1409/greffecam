<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DossierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SequestresRelationManager extends RelationManager
{
    protected static string $relationship = 'sequestres';

    protected static ?string $title = 'Séquestres';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('decision_id')
                ->label('Décision concernée')
                ->relationship('decision', 'numero_repertoire', fn($query) => $query->where('dossier_id', $this->getOwnerRecord()->id))
                ->required()
                ->searchable(),

            Forms\Components\Select::make('nature_sequestre_id')
                ->label('Nature du séquestre')
                ->relationship('natureSequestre', 'libelle')
                ->required(),

            Forms\Components\Select::make('statut_sequestre_id')
                ->label('Statut')
                ->relationship('statutSequestre', 'libelle')
                ->required(),

            Forms\Components\DatePicker::make('date_ouverture')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),

            Forms\Components\TextInput::make('taux_precompte')
                ->label('Taux de précompte (%)')
                ->numeric()
                ->step(0.01)
                ->suffix('%')
                ->required()
                ->dehydrateStateUsing(fn($state) => $state / 100)
                ->formatStateUsing(fn($state) => $state !== null ? $state * 100 : null),

            Forms\Components\Textarea::make('observations')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('numero_dossier_sequestre')
                    ->label('N° Dossier Séquestre')
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('decision.numero_repertoire')
                    ->label('Décision')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('natureSequestre.libelle')
                    ->label('Nature')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('statutSequestre.libelle')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statutSequestre?->couleur ?? 'gray'),

                Tables\Columns\TextColumn::make('taux_pourcentage')
                    ->label('Taux'),

                Tables\Columns\TextColumn::make('solde_actuel')
                    ->label('Solde')
                    ->money('XAF')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('date_ouverture')
                    ->label('Ouverture')
                    ->date('d/m/Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // ✅ CORRIGÉ : préciser le panel 'sequestre-caution', car SequestreResource
                // n'existe pas dans le panel 'decision-recours' où ce RelationManager s'affiche.
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn($record) => \App\Modules\SequestreCaution\Filament\Resources\SequestreResource::getUrl(
                        'view',
                        ['record' => $record],
                        panel: 'sequestre-caution'
                    ))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Aucun séquestre')
            ->emptyStateDescription('Créez un séquestre à partir d\'une décision de ce dossier.')
            ->emptyStateIcon('heroicon-o-lock-closed');
    }
}
