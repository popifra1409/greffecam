<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use App\Models\MotifMouvement;
use App\Models\MouvementSequestre;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MouvementsRelationManager extends RelationManager
{
    protected static string $relationship = 'mouvements';

    protected static ?string $title = 'Mouvements (Entrées / Sorties)';

    // ================================================================
    // FORMULAIRE
    // ================================================================
    public function form(Form $form): Form
    {
        return $form->schema([

            // ✅ Solde disponible toujours visible avant toute saisie
            Forms\Components\Placeholder::make('solde_disponible_info')
                ->label('💰 Solde disponible')
                ->content(fn() => number_format($this->getOwnerRecord()->fresh()->solde_actuel, 0, ',', ' ') . ' FCFA')
                ->columnSpanFull(),

            // Champ caché : id du mouvement en cours d'édition (permet de recalculer
            // correctement le solde disponible lors d'une modification).
            Forms\Components\Hidden::make('_editing_id'),

            Forms\Components\DatePicker::make('date_mouvement')
                ->label('Date')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->columnSpan(1),

            Forms\Components\Select::make('motif_mouvement_id')
                ->label('Motif')
                ->options(function (Get $get) {
                    if (filled($get('entree'))) {
                        return MotifMouvement::pourType('versement')->pluck('libelle', 'id');
                    }
                    if (filled($get('sortie'))) {
                        return MotifMouvement::pourType('retrait')->pluck('libelle', 'id');
                    }
                    return MotifMouvement::where('is_active', true)->pluck('libelle', 'id');
                })
                ->searchable()
                ->preload()
                ->live()
                ->createOptionForm([
                    Forms\Components\TextInput::make('libelle')
                        ->label('Libellé du motif')
                        ->required(),

                    Forms\Components\Select::make('type_mouvement')
                        ->label('Applicable à')
                        ->options([
                            'versement' => '⬇️ Versement uniquement',
                            'retrait' => '⬆️ Retrait uniquement',
                            'les_deux' => '↕️ Versement et Retrait',
                        ])
                        ->default('les_deux')
                        ->required(),
                ])
                ->createOptionUsing(function (array $data): int {
                    $motif = MotifMouvement::create([
                        'libelle' => $data['libelle'],
                        'type_mouvement' => $data['type_mouvement'],
                        'is_active' => true,
                    ]);

                    return $motif->id;
                })
                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                    return $action->modalHeading('Créer un nouveau motif');
                })
                ->columnSpan(2),

            // ✅ Colonne ENTREES — versement effectué par une partie adverse
            Forms\Components\TextInput::make('entree')
                ->label('ENTREES (FCFA)')
                ->numeric()
                ->minValue(1)
                ->prefix('⬇️')
                ->live(onBlur: true)
                ->disabled(fn(Get $get) => filled($get('sortie')))
                ->required(fn(Get $get) => empty($get('sortie')))
                ->rules([
                    fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if (filled($value) && filled($get('sortie'))) {
                            $fail('Vous ne pouvez pas remplir ENTREES et SORTIES en même temps.');
                        }
                    },
                ])
                ->helperText('Le précompte sera calculé automatiquement selon le taux du séquestre')
                ->columnSpan(1),

            // ✅ Colonne SORTIES — retrait limité au solde disponible
            Forms\Components\TextInput::make('sortie')
                ->label('SORTIES (FCFA)')
                ->numeric()
                ->minValue(1)
                ->prefix('⬆️')
                ->live(onBlur: true)
                ->disabled(fn(Get $get) => filled($get('entree')))
                ->required(fn(Get $get) => empty($get('entree')))
                ->rules([
                    fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $disponible = $this->soldeDisponible($get('_editing_id'));

                        if ((float) $value > $disponible) {
                            $fail('Le retrait (' . number_format((float) $value, 0, ',', ' ') . ' FCFA) dépasse le solde disponible (' . number_format($disponible, 0, ',', ' ') . ' FCFA).');
                        }
                    },
                ])
                ->helperText('Montant retiré du solde, limité au disponible')
                ->columnSpan(1),

            // ✅ Versement : sélection obligatoire de la partie adverse (payeur)
            Forms\Components\Select::make('sequestre_partie_adverse_id')
                ->label('Partie adverse (payeur)')
                ->options(fn() => $this->getOwnerRecord()->partiesAdverses->pluck('nom_complet', 'id'))
                ->searchable()
                ->required(fn(Get $get) => filled($get('entree')))
                ->visible(fn(Get $get) => filled($get('entree')) || (!filled($get('entree')) && !filled($get('sortie'))))
                ->live()
                ->columnSpanFull()
                ->helperText('Personne qui verse l\'argent (ex: locataire)'),

            // ✅ Retrait : sélection obligatoire de l'ayant droit (bénéficiaire légal)
            Forms\Components\Select::make('sequestre_ayant_droit_id')
                ->label('Ayant droit (bénéficiaire légal)')
                ->options(fn() => $this->getOwnerRecord()->ayantsDroit->pluck('nom_complet', 'id'))
                ->searchable()
                ->required(fn(Get $get) => filled($get('sortie')))
                ->visible(fn(Get $get) => filled($get('sortie')))
                ->live()
                ->columnSpanFull()
                ->helperText('Personne légalement bénéficiaire de ce retrait'),

            // ✅ Gestion de la procuration (retrait uniquement)
            Forms\Components\Toggle::make('est_procuration')
                ->label('Retrait effectué par procuration (tiers mandaté)')
                ->live()
                ->visible(fn(Get $get) => filled($get('sortie')))
                ->columnSpanFull(),

            Forms\Components\TextInput::make('mandataire_nom')
                ->label('Nom du mandataire')
                ->required(fn(Get $get) => $get('est_procuration'))
                ->visible(fn(Get $get) => filled($get('sortie')) && $get('est_procuration'))
                ->columnSpan(1),

            Forms\Components\TextInput::make('mandataire_reference_procuration')
                ->label('Référence de la procuration')
                ->required(fn(Get $get) => $get('est_procuration'))
                ->visible(fn(Get $get) => filled($get('sortie')) && $get('est_procuration'))
                ->helperText('N° acte, date, ou référence du document de procuration')
                ->columnSpan(1),
        ])->columns(2);
    }

    /**
     * Solde disponible pour validation, en tenant compte du mouvement en cours
     * d'édition (le cas échéant) : si on modifie un retrait existant, son
     * montant est remis dans le disponible avant de comparer la nouvelle valeur.
     */
    protected function soldeDisponible(mixed $editingId = null): float
    {
        $sequestre = $this->getOwnerRecord()->fresh();
        $solde = (float) $sequestre->solde_actuel;

        if (filled($editingId)) {
            $mouvement = MouvementSequestre::find($editingId);

            if ($mouvement && $mouvement->type_mouvement === 'retrait') {
                $solde += (float) $mouvement->montant_mouvement;
            }
        }

        return $solde;
    }

    /**
     * Convertit les champs virtuels du formulaire (entree/sortie, sélections
     * de partie adverse/ayant droit, procuration) en colonnes réelles de la
     * base de données, et calcule operateur_beneficiaire automatiquement.
     */
    protected function convertirDonneesFormulaire(array $data): array
    {
        $entree = $data['entree'] ?? null;
        $sortie = $data['sortie'] ?? null;

        if ($entree) {
            $data['type_mouvement'] = 'versement';
            $data['montant_mouvement'] = $entree;

            $partieAdverse = \App\Models\SequestrePartieAdverse::find($data['sequestre_partie_adverse_id'] ?? null);
            $data['operateur_beneficiaire'] = $partieAdverse?->nom_complet ?? 'Non renseigné';

            // Pas de procuration/ayant droit sur un versement
            $data['sequestre_ayant_droit_id'] = null;
            $data['est_procuration'] = false;
            $data['mandataire_nom'] = null;
            $data['mandataire_reference_procuration'] = null;
        } elseif ($sortie) {
            $data['type_mouvement'] = 'retrait';
            $data['montant_mouvement'] = $sortie;

            $ayantDroit = \App\Models\SequestreAyantDroit::find($data['sequestre_ayant_droit_id'] ?? null);

            if (!empty($data['est_procuration']) && !empty($data['mandataire_nom'])) {
                $data['operateur_beneficiaire'] = $data['mandataire_nom'] . ' (mandataire de ' . ($ayantDroit?->nom_complet ?? 'ayant droit') . ')';
            } else {
                $data['operateur_beneficiaire'] = $ayantDroit?->nom_complet ?? 'Non renseigné';
            }

            // Pas de partie adverse sur un retrait
            $data['sequestre_partie_adverse_id'] = null;
        }

        unset($data['entree'], $data['sortie'], $data['_editing_id']);

        return $data;
    }

    // ================================================================
    // TABLEAU
    // ================================================================
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

                Tables\Columns\IconColumn::make('est_procuration')
                    ->label('Procuration')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('')
                    ->trueColor('warning')
                    ->tooltip(fn($record) => $record->est_procuration
                        ? 'Réf. procuration : ' . ($record->mandataire_reference_procuration ?? '—')
                        : null),

                Tables\Columns\TextColumn::make('motifMouvement.libelle')
                    ->label('Motif')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('montant_mouvement')
                    ->label('ENTREES')
                    ->state(fn($record) => $record->type_mouvement === 'versement' ? $record->montant_mouvement : null)
                    ->money('XAF')
                    ->color('success')
                    ->placeholder('—')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('montant_sortie')
                    ->label('SORTIES')
                    ->state(fn($record) => $record->type_mouvement === 'retrait' ? $record->montant_mouvement : null)
                    ->money('XAF')
                    ->color('danger')
                    ->placeholder('—')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('montant_precompte')
                    ->label('Montant Séquestre')
                    ->money('XAF')
                    ->color('warning')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('solde_apres')
                    ->label('Solde')
                    ->money('XAF')
                    ->weight('bold')
                    ->alignEnd()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),

                // ✅ Indique si la décharge signée a été archivée pour un retrait
                Tables\Columns\IconColumn::make('decharge_jointe')
                    ->label('Décharge')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->visible(fn($record) => $record?->type_mouvement === 'retrait')
                    ->tooltip(fn($record) => $record->decharge_jointe
                        ? 'Décharge signée déjà archivée'
                        : 'Décharge signée non encore archivée'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('➕ Nouveau mouvement')
                    ->modalHeading('Enregistrer un mouvement')
                    ->modalWidth('2xl')
                    ->mutateFormDataUsing(fn(array $data): array => $this->convertirDonneesFormulaire($data)),

                // ✅ Rapport des mouvements de ce séquestre (PDF)
                Tables\Actions\Action::make('rapport_pdf')
                    ->label('📊 Rapport PDF')
                    ->color('gray')
                    ->url(fn() => route('sequestres.etat.pdf', ['sequestre' => $this->getOwnerRecord()->id]))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                // ✅ Joindre la décharge signée scannée (retraits uniquement)
                Tables\Actions\Action::make('joindre_decharge')
                    ->label(fn($record) => $record->decharge_jointe ? 'Décharge (voir/remplacer)' : 'Joindre décharge')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color(fn($record) => $record->decharge_jointe ? 'gray' : 'warning')
                    ->visible(fn($record) => $record->type_mouvement === 'retrait')
                    ->form([
                        Forms\Components\FileUpload::make('fichier_path')
                            ->label('Décharge signée (scan)')
                            ->disk('local')
                            ->directory('sequestre-documents')
                            ->visibility('private')
                            ->required()
                            ->downloadable()
                            ->openable()
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                    ])
                    ->action(function (array $data, $record): void {
                        \App\Models\SequestreDocument::create([
                            'sequestre_id' => $this->getOwnerRecord()->id,
                            'mouvement_sequestre_id' => $record->id,
                            'categorie' => 'quittance',
                            'libelle' => 'Décharge — ' . $record->operateur_beneficiaire . ' — ' . $record->date_mouvement->format('d/m/Y'),
                            'fichier_path' => $data['fichier_path'],
                            'depose_par' => auth()->id(),
                        ]);
                    })
                    ->modalHeading('Archiver la décharge signée')
                    ->successNotificationTitle('Décharge archivée dans le sous-dossier Quittances'),

                Tables\Actions\Action::make('recu_pdf')
                    ->label('Reçu')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn($record) => route('mouvements.recu.pdf', ['mouvement' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl')
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        $data['_editing_id'] = $record->id;

                        if ($record->type_mouvement === 'versement') {
                            $data['entree'] = $record->montant_mouvement;
                        } else {
                            $data['sortie'] = $record->montant_mouvement;
                        }

                        return $data;
                    })
                    ->mutateFormDataUsing(fn(array $data): array => $this->convertirDonneesFormulaire($data)),

                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('date_mouvement', 'desc')
            ->poll('10s');
    }
}
