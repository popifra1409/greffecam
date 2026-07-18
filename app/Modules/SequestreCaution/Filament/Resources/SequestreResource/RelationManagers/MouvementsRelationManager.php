<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use App\Models\MotifMouvement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MouvementsRelationManager extends RelationManager
{
    protected static string $relationship = 'mouvements';

    protected static ?string $title = 'Mouvements (Entrées / Sorties)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date_mouvement')
                ->label('Date')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now()),

            Forms\Components\Select::make('type_mouvement')
                ->label('Type')
                ->options([
                    'versement' => '⬇️ Versement (Entrée)',
                    'retrait' => '⬆️ Retrait (Sortie)',
                ])
                ->required()
                ->live()
                ->native(false),

            Forms\Components\TextInput::make('operateur_beneficiaire')
                ->label('Opérateur / Bénéficiaire')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('motif_mouvement_id')
                ->label('Motif')
                ->options(function (Forms\Get $get) {
                    $type = $get('type_mouvement') ?? 'les_deux';
                    return MotifMouvement::pourType($type)->pluck('libelle', 'id');
                })
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('montant_mouvement')
                ->label('Montant (FCFA)')
                ->numeric()
                ->required()
                ->minValue(1)
                ->prefix('FCFA')
                ->helperText(fn(Forms\Get $get) => $get('type_mouvement') === 'versement'
                    ? 'Le précompte sera calculé automatiquement selon le taux du séquestre'
                    : 'Montant retiré du solde'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('operateur_beneficiaire')
            ->columns([
                Tables\Columns\TextColumn::make('date_mouvement')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('operateur_beneficiaire')
                    ->label('Opérateur / Bénéficiaire')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('motifMouvement.libelle')
                    ->label('Motif')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn($record) => $record->type_mouvement === 'versement' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('montant_mouvement')
                    ->label('Montant')
                    ->money('XAF')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('montant_precompte')
                    ->label('Précompte')
                    ->money('XAF')
                    ->color('warning')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('montant_net')
                    ->label('Impact net')
                    ->money('XAF')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('solde_apres')
                    ->label('Solde')
                    ->money('XAF')
                    ->weight('bold')
                    ->alignEnd()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('➕ Nouveau mouvement')
                    ->modalHeading('Enregistrer un mouvement'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('date_mouvement', 'desc')
            ->poll('10s'); // rafraîchit le solde si modifié ailleurs
    }
}
