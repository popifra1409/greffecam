<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecoursResource\Pages;
use App\Models\Recours;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class RecoursResource extends Resource
{
    protected static ?string $model = Recours::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Recours';

    protected static ?string $pluralModelLabel = 'Recours';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations Générales')
                            ->schema([
                                Forms\Components\Section::make('Identification du recours')
                                    ->schema([
                                        Forms\Components\TextInput::make('numero_recours')
                                            ->label('Numéro du recours')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->default(fn() => 'REC/' . now()->year . '/' . str_pad(Recours::whereYear('created_at', now()->year)->count() + 1, 6, '0', STR_PAD_LEFT))
                                            ->maxLength(255),

                                        Forms\Components\Select::make('annee_judiciaire_id')
                                            ->label('Année judiciaire')
                                            ->relationship('anneeJudiciaire', 'libelle', function ($query) {
                                                return $query->where('is_active', true)
                                                    ->orWhere('is_cloturee', false);
                                            })
                                            ->default(function () {
                                                return \App\Models\AnneeJudiciaire::where('is_active', true)->first()?->id;
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('decision_id')
                                            ->label('Décision attaquée')
                                            ->relationship('decision', 'numero_rg', function ($query) {
                                                return $query->where('statut', 'enregistree')
                                                    ->orWhere('statut', 'signee');
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $decision = \App\Models\Decision::find($state);
                                                    if ($decision) {
                                                        $set('date_decision_attaquee', $decision->date_decision);

                                                        // Recalculer la date limite si le type de recours est aussi défini
                                                        $typeRecoursId = $get('type_recours_id');
                                                        if ($typeRecoursId && $decision->date_decision) {
                                                            $typeRecours = \App\Models\TypeRecours::find($typeRecoursId);
                                                            if ($typeRecours) {
                                                                $dateLimite = \App\Services\DelaiCalculator::calculerDateLimite(
                                                                    \Carbon\Carbon::parse($decision->date_decision),
                                                                    $typeRecours->delai_jours
                                                                );
                                                                $set('date_limite_recours', $dateLimite->format('Y-m-d'));
                                                            }
                                                        }
                                                    }
                                                }
                                            })
                                            ->helperText('Seules les décisions enregistrées ou signées peuvent faire l\'objet d\'un recours'),

                                        Forms\Components\Select::make('type_recours_id')
                                            ->label('Type de recours')
                                            ->relationship('typeRecours', 'libelle')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $dateDecisionAttaquee = $get('date_decision_attaquee');
                                                    if ($dateDecisionAttaquee) {
                                                        $typeRecours = \App\Models\TypeRecours::find($state);
                                                        if ($typeRecours) {
                                                            $dateLimite = \App\Services\DelaiCalculator::calculerDateLimite(
                                                                \Carbon\Carbon::parse($dateDecisionAttaquee),
                                                                $typeRecours->delai_jours
                                                            );
                                                            $set('date_limite_recours', $dateLimite->format('Y-m-d'));
                                                        }
                                                    }
                                                }
                                            }),
                                    ])->columns(2),

                                Forms\Components\Section::make('Parties au recours')
                                    ->schema([
                                        Forms\Components\TextInput::make('appelant')
                                            ->label('Appelant / Requérant')
                                            ->maxLength(255)
                                            ->helperText('La partie qui interjette le recours'),

                                        Forms\Components\TextInput::make('intime')
                                            ->label('Intimé / Défendeur')
                                            ->maxLength(255)
                                            ->helperText('La partie adverse'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Dates et délais')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_decision_attaquee')
                                            ->label('Date de la décision attaquée')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $typeRecoursId = $get('type_recours_id');
                                                if ($state && $typeRecoursId) {
                                                    $typeRecours = \App\Models\TypeRecours::find($typeRecoursId);
                                                    if ($typeRecours) {
                                                        $dateLimite = \App\Services\DelaiCalculator::calculerDateLimite(
                                                            \Carbon\Carbon::parse($state),
                                                            $typeRecours->delai_jours
                                                        );
                                                        $set('date_limite_recours', $dateLimite->format('Y-m-d'));
                                                    }
                                                }
                                            }),

                                        Forms\Components\DatePicker::make('date_interjetee')
                                            ->label('Date d\'interjection du recours')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->default(now()),

                                        Forms\Components\DatePicker::make('date_limite_recours')
                                            ->label('Date limite légale')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->helperText('Calculée automatiquement selon le type de recours (hors week-ends et jours fériés)'),

                                        Forms\Components\DatePicker::make('date_notification')
                                            ->label('Date de notification')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Recevabilité')
                            ->schema([
                                Forms\Components\Section::make('Examen de la recevabilité')
                                    ->schema([
                                        Forms\Components\Select::make('statut_recevabilite')
                                            ->label('Statut de recevabilité')
                                            ->options([
                                                'en_cours_examen' => 'En cours d\'examen',
                                                'recevable' => 'Recevable',
                                                'irrecevable' => 'Irrecevable',
                                            ])
                                            ->default('en_cours_examen')
                                            ->required()
                                            ->live(),

                                        Forms\Components\DatePicker::make('date_decision_recevabilite')
                                            ->label('Date de décision sur la recevabilité')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->visible(fn(Get $get) => in_array($get('statut_recevabilite'), ['recevable', 'irrecevable'])),

                                        Forms\Components\Textarea::make('motif_irrecevabilite')
                                            ->label('Motif d\'irrecevabilité')
                                            ->maxLength(65535)
                                            ->rows(3)
                                            ->visible(fn(Get $get) => $get('statut_recevabilite') === 'irrecevable')
                                            ->required(fn(Get $get) => $get('statut_recevabilite') === 'irrecevable')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Workflow & Observations')
                            ->schema([
                                Forms\Components\Section::make('État du recours')
                                    ->schema([
                                        Forms\Components\Select::make('statut_global')
                                            ->label('Statut global')
                                            ->options([
                                                'en_cours' => 'En cours',
                                                'cloture' => 'Clôturé',
                                                'abandonne' => 'Abandonné',
                                            ])
                                            ->default('en_cours')
                                            ->required(),

                                        Forms\Components\TextInput::make('etape_actuelle')
                                            ->label('Étape actuelle')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->maxValue(11)
                                            ->helperText('Sera géré automatiquement via le workflow'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Observations')
                                    ->schema([
                                        Forms\Components\Textarea::make('observations')
                                            ->label('Observations générales')
                                            ->maxLength(65535)
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Gestion')
                                    ->schema([
                                        Forms\Components\Select::make('greffier_responsable_id')
                                            ->label('Greffier responsable')
                                            ->relationship('greffierResponsable', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(auth()->id()),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_recours')
                    ->label('N° Recours')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('decision.numero_rg')
                    ->label('Décision attaquée')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => $record->decision_id ? route('filament.admin.resources.decisions.view', $record->decision_id) : null)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('typeRecours.libelle')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_interjetee')
                    ->label('Date interjetée')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_limite_recours')
                    ->label('Date limite')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jours_restants')
                    ->label('Délai')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->jours_restants . ' j')
                    ->color(fn($record) => match ($record->niveau_alerte) {
                        'rouge' => 'danger',
                        'orange' => 'warning',
                        'jaune' => 'info',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('statut_recevabilite')
                    ->label('Recevabilité')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'recevable' => 'success',
                        'irrecevable' => 'danger',
                        'en_cours_examen' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'recevable' => 'Recevable',
                        'irrecevable' => 'Irrecevable',
                        'en_cours_examen' => 'En examen',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('etape_actuelle')
                    ->label('Étape')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => "Étape {$state}/11")
                    ->sortable(),

                Tables\Columns\TextColumn::make('statut_global')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'en_cours' => 'warning',
                        'cloture' => 'success',
                        'abandonne' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'en_cours' => 'En cours',
                        'cloture' => 'Clôturé',
                        'abandonne' => 'Abandonné',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_recours_id')
                    ->label('Type de recours')
                    ->relationship('typeRecours', 'libelle'),

                Tables\Filters\SelectFilter::make('statut_recevabilite')
                    ->label('Recevabilité')
                    ->options([
                        'en_cours_examen' => 'En examen',
                        'recevable' => 'Recevable',
                        'irrecevable' => 'Irrecevable',
                    ]),

                Tables\Filters\SelectFilter::make('statut_global')
                    ->label('Statut')
                    ->options([
                        'en_cours' => 'En cours',
                        'cloture' => 'Clôturé',
                        'abandonne' => 'Abandonné',
                    ]),

                Tables\Filters\Filter::make('alertes')
                    ->label('Alertes délais')
                    ->query(fn($query) => $query->whereHas('alertes', function ($q) {
                        $q->where('est_lue', false);
                    })),
            ])
            ->actions([
                Tables\Actions\Action::make('etape_suivante')
                    ->label('Étape suivante')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->statut_global === 'en_cours' && $record->etape_actuelle < 11)
                    ->requiresConfirmation()
                    ->modalHeading('Passer à l\'étape suivante')
                    ->modalDescription(fn($record) => 'Voulez-vous compléter l\'étape ' . $record->etape_actuelle . ' et passer à l\'étape suivante ?')
                    ->action(function ($record) {
                        $record->passerEtapeSuivante();

                        \Filament\Notifications\Notification::make()
                            ->title('Étape complétée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecours::route('/'),
            'create' => Pages\CreateRecours::route('/create'),
            'edit' => Pages\EditRecours::route('/{record}/edit'),
            'view' => Pages\ViewRecours::route('/{record}'),
        ];
    }
}
