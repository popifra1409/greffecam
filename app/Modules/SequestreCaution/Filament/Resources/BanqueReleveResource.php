<?php

namespace App\Modules\SequestreCaution\Filament\Resources;

use App\Models\BanqueReleve;
use App\Models\Sequestre;
use App\Modules\SequestreCaution\Filament\Resources\BanqueReleveResource\Pages;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class BanqueReleveResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = BanqueReleve::class;

    protected static ?string $slug = 'banque';

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Banque';

    protected static ?string $modelLabel = 'Relevé bancaire';

    protected static ?string $pluralModelLabel = 'Relevés bancaires';

    protected static ?int $navigationSort = 10;

    protected static bool $hasTitleCaseModelLabel = false;

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

    /**
     * Libellé d'un compte dans les listes déroulantes :
     * "TPI-YDCA/SEQ/26/000001 — Dossier Famille NGO (Déc. 654)".
     */
    public static function libelleCompte(Sequestre $sequestre): string
    {
        $numeroDecision = $sequestre->decision?->numero_repertoire;

        return ($sequestre->numero_dossier_sequestre ?? 'Séquestre #' . $sequestre->id)
            . ' — ' . $sequestre->intitule
            . ($numeroDecision ? ' (Déc. ' . $numeroDecision . ')' : '');
    }

    protected static function optionsComptes(): array
    {
        return Sequestre::query()
            ->with(['decision', 'dossier', 'natureSequestre', 'representant'])
            ->orderBy('numero_dossier_sequestre')
            ->limit(300)
            ->get()
            ->mapWithKeys(fn(Sequestre $s) => [$s->id => static::libelleCompte($s)])
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Compte concerné')
                ->schema([
                    Forms\Components\Select::make('sequestre_id')
                        ->label('Compte (N° séquestre / N° décision)')
                        ->required()
                        ->searchable()
                        ->live()
                        ->preload()
                        ->options(fn(): array => static::optionsComptes())
                        ->getSearchResultsUsing(function (string $search): array {
                            return Sequestre::query()
                                ->with(['decision', 'dossier', 'natureSequestre', 'representant'])
                                ->where(function ($query) use ($search) {
                                    $query->where('numero_dossier_sequestre', 'like', "%{$search}%")
                                        ->orWhere('nom_intitule', 'like', "%{$search}%")
                                        ->orWhereHas('decision', fn($q) => $q->where('numero_repertoire', 'like', "%{$search}%"))
                                        ->orWhereHas('dossier', fn($q) => $q->where('numero_dossier', 'like', "%{$search}%"));
                                })
                                ->orderBy('numero_dossier_sequestre')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn(Sequestre $s) => [$s->id => static::libelleCompte($s)])
                                ->all();
                        })
                        ->getOptionLabelUsing(fn($value): ?string => ($sequestre = Sequestre::with('decision')->find($value))
                            ? static::libelleCompte($sequestre)
                            : null)
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('compte_info')
                        ->label('')
                        ->content(function (Get $get) {
                            $sequestre = Sequestre::with(['decision', 'dossier', 'natureSequestre', 'representant'])
                                ->find($get('sequestre_id'));

                            if (! $sequestre) {
                                return 'Sélectionnez un compte pour afficher ses informations.';
                            }

                            return new HtmlString(
                                '<div style="font-family: monospace; line-height: 2;">'
                                . '<strong>Intitulé du séquestre :</strong> ' . e($sequestre->intitule) . '<br>'
                                . '<strong>Numéro de compte :</strong> ' . e($sequestre->numero_compte_bancaire ?: '— (compte à ouvrir)') . '<br>'
                                . '<strong>Date d\'ouverture du compte :</strong> ' . ($sequestre->date_ouverture_compte?->format('d/m/Y') ?? '—') . '<br>'
                                . '<strong>Solde au séquestre (ce jour) :</strong> ' . number_format($sequestre->solde_actuel, 0, ',', ' ') . ' FCFA'
                                . '</div>'
                            );
                        })
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => filled($get('sequestre_id'))),
                ]),

            Forms\Components\Section::make('Relevé fourni par la banque')
                ->schema([
                    Forms\Components\DatePicker::make('date_releve')
                        ->label('Date d\'arrêté du relevé')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now())
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where('sequestre_id', $get('sequestre_id')),
                        )
                        ->validationMessages([
                            'unique' => 'Un relevé existe déjà pour ce compte à cette date.',
                        ])
                        ->helperText('Date libre, telle qu\'annoncée par la banque.'),

                    Forms\Components\TextInput::make('solde_banque')
                        ->label('Solde fourni par la banque')
                        ->numeric()
                        ->required()
                        ->suffix('FCFA'),

                    Forms\Components\Textarea::make('observations')
                        ->label('Observations')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('saisi_par')
                        ->default(fn() => auth()->id()),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_releve')
                    ->label('Date d\'arrêté')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sequestre.numero_dossier_sequestre')
                    ->label('N° Séquestre')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sequestre_intitule')
                    ->label('Intitulé du compte')
                    ->getStateUsing(fn($record) => $record->sequestre?->intitule)
                    ->wrap(),

                Tables\Columns\TextColumn::make('numero_compte')
                    ->label('N° de compte')
                    ->getStateUsing(fn($record) => $record->sequestre?->numero_compte_bancaire)
                    ->placeholder('À ouvrir')
                    ->badge()
                    ->color(fn($state) => filled($state) ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('solde_banque')
                    ->label('Solde banque')
                    ->money('XAF')
                    ->alignEnd()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('saisiPar.name')
                    ->label('Saisi par')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('observations')
                    ->label('Observations')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sequestre_id')
                    ->label('Compte')
                    ->relationship('sequestre', 'numero_dossier_sequestre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->defaultSort('date_releve', 'desc')
            ->emptyStateHeading('Aucun relevé bancaire')
            ->emptyStateDescription('Enregistrez le premier solde annoncé par la banque.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanqueReleves::route('/'),
            'create' => Pages\CreateBanqueReleve::route('/create'),
            'edit' => Pages\EditBanqueReleve::route('/{record}/edit'),
        ];
    }
}
