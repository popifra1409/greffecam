<?php

namespace App\Modules\SequestreCaution\Filament\Resources;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;
use App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;
use App\Models\Sequestre;
use App\Models\Decision;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SequestreResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Sequestre::class;
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationGroup = 'Gestion des Séquestres';
    protected static ?string $modelLabel = 'Séquestre';
    protected static ?string $pluralModelLabel = 'Séquestres';
    protected static ?int $navigationSort = 1;

    protected static function getViewPermission(): string
    {
        return 'view_sequestres';
    }

    protected static function getCreatePermission(): string
    {
        return 'create_sequestres';
    }

    protected static function getEditPermission(): string
    {
        return 'edit_sequestres';
    }

    protected static function getDeletePermission(): string
    {
        return 'delete_sequestres';
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MouvementsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    /**
     * Format : "TPI-YDCA/CIV/RE/26/00000001 - Dec. 654"
     */
    public static function formaterLabelDecision(Decision $decision): string
    {
        $numeroDossier = $decision->dossier?->numero_dossier ?? 'Dossier N/A';
        $numeroDecision = $decision->numero_repertoire ?? '-';

        return "{$numeroDossier} - Déc. {$numeroDecision}";
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Placeholder::make('numero_dossier_sequestre_display')
                ->label('N° Dossier Séquestre')
                ->content(fn($record) => $record?->numero_dossier_sequestre ?? 'Généré automatiquement à la création')
                ->visible(fn($record) => $record !== null),

            Forms\Components\Tabs::make('Sequestre')
                ->columnSpanFull()
                ->tabs([

                    Forms\Components\Tabs\Tab::make('Décision & Dossier')
                        ->icon('heroicon-o-scale')
                        ->schema([
                            Forms\Components\Select::make('decision_id')
                                ->label('Décision judiciaire')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                // ✅ Pré-remplissage automatique : si on arrive depuis ViewDossier
                                // avec un dossier n'ayant qu'UNE SEULE décision, on la sélectionne d'office
                                ->default(function () {
                                    $dossierId = request()->query('dossier_id');

                                    if (!$dossierId) {
                                        return null;
                                    }

                                    $decisions = Decision::where('dossier_id', $dossierId)->pluck('id');

                                    return $decisions->count() === 1 ? $decisions->first() : null;
                                })
                                ->getSearchResultsUsing(function (string $search) {
                                    $dossierId = request()->query('dossier_id');

                                    return Decision::query()
                                        ->with('dossier')
                                        ->when($dossierId, fn($query) => $query->where('dossier_id', $dossierId))
                                        ->where(function ($query) use ($search) {
                                            $query->whereHas('dossier', fn($q) => $q->where('numero_dossier', 'like', "%{$search}%"))
                                                ->orWhere('numero_repertoire', 'like', "%{$search}%");
                                        })
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn($decision) => [
                                            $decision->id => static::formaterLabelDecision($decision),
                                        ]);
                                })
                                ->getOptionLabelUsing(function ($value) {
                                    $decision = Decision::with('dossier')->find($value);
                                    return $decision ? static::formaterLabelDecision($decision) : null;
                                })
                                ->options(function () {
                                    $dossierId = request()->query('dossier_id');

                                    return Decision::with('dossier')
                                        ->when($dossierId, fn($query) => $query->where('dossier_id', $dossierId))
                                        ->latest()
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(fn($decision) => [
                                            $decision->id => static::formaterLabelDecision($decision),
                                        ]);
                                })
                                ->helperText(function () {
                                    $dossierId = request()->query('dossier_id');
                                    return $dossierId
                                        ? 'Liste restreinte aux décisions de ce dossier.'
                                        : 'Sélectionnez la décision déjà rendue à l\'origine de ce séquestre.';
                                }),

                            Forms\Components\Placeholder::make('dossier_info')
                                ->label('')
                                ->content(function (Get $get) {
                                    $decisionId = $get('decision_id');
                                    if (!$decisionId) return 'Sélectionnez une décision pour voir les informations du dossier';

                                    $decision = Decision::with(['dossier', 'typeDecision', 'natureDecision'])->find($decisionId);
                                    if (!$decision) return '';

                                    return new \Illuminate\Support\HtmlString(
                                        '<div style="font-family: monospace; line-height: 2;">' .
                                            '<strong>Dossier :</strong> ' . ($decision->dossier?->numero_dossier ?? 'N/A') . '<br>' .
                                            '<strong>Intitulé :</strong> ' . ($decision->dossier?->demandeurs_liste ?: $decision->dossier?->demandeur_nom_complet ?? 'N/A') . '<br>' .
                                            '<strong>Type décision :</strong> ' . ($decision->typeDecision?->libelle ?? 'N/A') . '<br>' .
                                            '<strong>Nature décision :</strong> ' . ($decision->natureDecision?->libelle ?? 'N/A') . '<br>' .
                                            '<strong>Date décision :</strong> ' . ($decision->date_decision?->format('d/m/Y') ?? 'N/A') .
                                            '</div>'
                                    );
                                })
                                ->columnSpanFull()
                                ->visible(fn(Get $get) => $get('decision_id')),

                            Forms\Components\Select::make('dossier_partie_id')
                                ->label('Représentant de la famille')
                                ->options(function (Get $get) {
                                    $decisionId = $get('decision_id');
                                    if (!$decisionId) return [];

                                    $decision = Decision::find($decisionId);
                                    if (!$decision?->dossier_id) return [];

                                    return \App\Models\DossierPartie::where('dossier_id', $decision->dossier_id)
                                        ->get()
                                        ->mapWithKeys(fn($partie) => [$partie->id => $partie->nom_complet . ' (' . $partie->type_label . ')']);
                                })
                                ->searchable()
                                ->helperText('Partie déjà enrôlée dans ce dossier, désignée comme représentant'),
                        ]),

                    Forms\Components\Tabs\Tab::make('Caractéristiques')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Forms\Components\Select::make('nature_sequestre_id')
                                ->label('Nature')
                                ->relationship('natureSequestre', 'libelle')
                                ->required()
                                ->preload()
                                ->live(),

                            Forms\Components\Select::make('statut_sequestre_id')
                                ->label('Statut')
                                ->relationship('statutSequestre', 'libelle')
                                ->required()
                                ->preload(),

                            Forms\Components\DatePicker::make('date_ouverture')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(now()),

                            Forms\Components\TextInput::make('taux_precompte')
                                ->label('Taux de précompte')
                                ->numeric()
                                ->step(0.01)
                                ->suffix('%')
                                ->required()
                                ->minValue(0)
                                ->maxValue(100)
                                ->dehydrateStateUsing(fn($state) => $state / 100)
                                ->formatStateUsing(fn($state) => $state !== null ? $state * 100 : null)
                                ->helperText('Pourcentage précompté sur chaque versement, selon la décision de justice'),

                            Forms\Components\Textarea::make('observations')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('Ayants droit')
                        ->icon('heroicon-o-user-group')
                        ->badge(fn($record) => $record?->ayantsDroit?->count())
                        ->schema([
                            Forms\Components\Placeholder::make('libelle_ayants_droit_info')
                                ->label('')
                                ->content(fn(Get $get) => 'Groupe : ' . (
                                    \App\Models\NatureSequestre::find($get('nature_sequestre_id'))?->terme_ayants_droit ?: 'Ayants droit'
                                ))
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('ayantsDroit')
                                ->label('')
                                ->relationship('ayantsDroit')
                                ->schema([
                                    Forms\Components\TextInput::make('nom_complet')->label('Nom complet')->columnSpan(2),
                                    Forms\Components\TextInput::make('numero_cni')->label('N° CNI'),
                                    Forms\Components\TextInput::make('telephone')->label('Téléphone')->tel(),
                                    Forms\Components\TextInput::make('adresse')->label('Adresse')->columnSpanFull(),
                                ])
                                ->columns(4)
                                ->itemLabel(fn(array $state): ?string => $state['nom_complet'] ?? 'Nouvel ayant droit')
                                ->collapsible()
                                ->addActionLabel('➕ Ajouter un ayant droit')
                                ->columnSpanFull()
                                ->helperText('Optionnel — laissez vide si non applicable à ce stade'),
                        ]),

                    Forms\Components\Tabs\Tab::make('Parties adverses')
                        ->icon('heroicon-o-home')
                        ->badge(fn($record) => $record?->partiesAdverses?->count())
                        ->schema([
                            Forms\Components\Placeholder::make('libelle_parties_adverses_info')
                                ->label('')
                                ->content(fn(Get $get) => 'Groupe : ' . (
                                    \App\Models\NatureSequestre::find($get('nature_sequestre_id'))?->terme_parties_adverses ?: 'Parties adverses (payeurs)'
                                ))
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('partiesAdverses')
                                ->label('')
                                ->relationship('partiesAdverses')
                                ->schema([
                                    Forms\Components\TextInput::make('nom_complet')->label('Nom complet')->columnSpan(2),
                                    Forms\Components\TextInput::make('numero_cni')->label('N° CNI'),
                                    Forms\Components\TextInput::make('telephone')->label('Téléphone')->tel(),
                                    Forms\Components\TextInput::make('adresse')->label('Adresse')->columnSpanFull(),

                                    // ✅ Échéancier (remplace montant_loyer_attendu / jour_echeance)
                                    Forms\Components\DatePicker::make('date_debut_paiement')
                                        ->label('Date de début des paiements')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->helperText('Généralement la date du contrat de bail suite à la décision'),

                                    Forms\Components\TextInput::make('montant_echeance')
                                        ->label('Montant par échéance')
                                        ->numeric()
                                        ->suffix('FCFA')
                                        ->helperText('Montant dû à chaque échéance selon la périodicité'),

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
                                        ->placeholder('Laisser vide si durée indéterminée')
                                        ->helperText('Optionnel — sinon l\'échéancier est glissant'),
                                ])
                                ->columns(4)
                                ->itemLabel(fn(array $state): ?string => $state['nom_complet'] ?? 'Nouvelle partie adverse')
                                ->collapsible()
                                ->addActionLabel('➕ Ajouter une partie adverse')
                                ->columnSpanFull()
                                ->helperText('Optionnel — laissez vide si non applicable à ce stade'),
                        ]),

                    Forms\Components\Tabs\Tab::make('Partie Tierce')
                        ->icon('heroicon-o-briefcase')
                        ->badge(fn($record) => $record?->partiesTierces?->count())
                        ->schema([
                            Forms\Components\Placeholder::make('libelle_partie_tierce_info')
                                ->label('')
                                ->content(fn(Get $get) => 'Groupe : ' . (
                                    \App\Models\NatureSequestre::find($get('nature_sequestre_id'))?->terme_partie_tierce ?: 'Partie Tierce (Huissier, Avocat, Services)'
                                ))
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('partiesTierces')
                                ->label('')
                                ->relationship('partiesTierces')
                                ->schema([
                                    Forms\Components\Select::make('type_partie_tierce')
                                        ->label('Type')
                                        ->options([
                                            'huissier' => 'Huissier',
                                            'avocat' => 'Avocat',
                                            'service_public' => 'Service public (ENEO, CAMWATER...)',
                                            'autre' => 'Autre',
                                        ])
                                        ->default('autre'),

                                    Forms\Components\TextInput::make('nom_complet')
                                        ->label('Nom / Raison sociale')
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('telephone')
                                        ->label('Téléphone')
                                        ->tel(),

                                    Forms\Components\TextInput::make('reference')
                                        ->label('Référence')
                                        ->placeholder('N° facture, n° dossier, contrat...'),

                                    Forms\Components\TextInput::make('adresse')
                                        ->label('Adresse')
                                        ->columnSpanFull(),
                                ])
                                ->columns(4)
                                ->itemLabel(fn(array $state): ?string => $state['nom_complet'] ?? 'Nouvelle partie tierce')
                                ->collapsible()
                                ->addActionLabel('➕ Ajouter une partie tierce')
                                ->columnSpanFull()
                                ->helperText('Optionnel — huissier, avocat, service public, etc.'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->label('N° Dossier')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('intitule')
                    ->label('Intitulé')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('natureSequestre.libelle')
                    ->label('Nature')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('decision.numero_repertoire')
                    ->label('N° Décision')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('date_ouverture')
                    ->label('Ouverture')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('taux_pourcentage')
                    ->label('Taux'),

                Tables\Columns\TextColumn::make('solde_actuel')
                    ->label('Solde')
                    ->money('XAF')
                    ->sortable()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('statutSequestre.libelle')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statutSequestre?->couleur ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nature_sequestre_id')
                    ->label('Nature')
                    ->relationship('natureSequestre', 'libelle'),

                Tables\Filters\SelectFilter::make('statut_sequestre_id')
                    ->label('Statut')
                    ->relationship('statutSequestre', 'libelle'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('date_ouverture', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSequestres::route('/'),
            'create' => Pages\CreateSequestre::route('/create'),
            'edit' => Pages\EditSequestre::route('/{record}/edit'),
            'view' => Pages\ViewSequestre::route('/{record}'),
        ];
    }
}
