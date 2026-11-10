<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use App\Services\RepartitionSuccessionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
        // La répartition est recalculée à chaque rendu (création/édition/suppression
        // d'un ayant droit ou variation du solde), puis indexée par ayant droit.
        $sequestre = $this->getOwnerRecord();
        $sequestre->load('ayantsDroit');

        $repartition = app(RepartitionSuccessionService::class)->calculerRepartition($sequestre);
        $parts = $repartition['repartitions']->keyBy(fn(array $ligne) => $ligne['ayant_droit']->getKey());
        $soldeDisponible = (float) $repartition['solde_disponible'];
        $totalAttribue = (float) $repartition['repartitions']->sum('montant');

        return $table
            ->recordTitleAttribute('nom_complet')
            ->description(function () use ($repartition, $soldeDisponible, $totalAttribue): string {
                if ($soldeDisponible <= 0 || $repartition['repartitions']->isEmpty()) {
                    return 'Aucun partage calculable : ' . ($repartition['avertissement'] ?? 'solde indisponible.');
                }

                return 'Solde à répartir : ' . number_format($soldeDisponible, 0, ',', ' ')
                    . ' FCFA — attribué : ' . number_format($totalAttribue, 0, ',', ' ') . ' FCFA';
            })
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('role_part')
                    ->label('Rôle')
                    ->badge()
                    ->getStateUsing(function ($record) use ($parts): string {
                        $ligne = $parts->get($record->getKey());

                        return $ligne ? $ligne['role_label'] : $record->role_succession_label;
                    })
                    ->color(fn(string $state) => match ($state) {
                        'Conjoint' => 'info',
                        'Enfant' => 'success',
                        'Personnalisé' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('part_pourcentage_calculee')
                    ->label('Part (%)')
                    ->alignEnd()
                    ->getStateUsing(fn($record) => ($ligne = $parts->get($record->getKey())) ? $ligne['part_pourcentage'] : null)
                    ->formatStateUsing(fn($state) => $state === null ? '—' : number_format((float) $state, 2, ',', ' ') . ' %'),

                Tables\Columns\TextColumn::make('part_montant')
                    ->label('Part du solde')
                    ->money('XAF')
                    ->weight('bold')
                    ->alignEnd()
                    ->getStateUsing(fn($record) => ($ligne = $parts->get($record->getKey())) ? $ligne['montant'] : null)
                    ->color(fn($record) => filled($parts->get($record->getKey())['montant'] ?? null) ? 'success' : 'gray')
                    ->placeholder('—'),

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
                        'resultat' => app(RepartitionSuccessionService::class)
                            ->calculerRepartition($this->getOwnerRecord()->load('ayantsDroit')),
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
