<?php

namespace App\Modules\DecisionRecours\Filament\Resources;

use App\Modules\DecisionRecours\Filament\Resources\RecoursResource\Pages;
use App\Models\Recours;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecoursResource extends Resource
{
    protected static ?string $model = Recours::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Recours';

    protected static ?string $pluralModelLabel = 'Recours';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identification du recours')
                    ->schema([
                        Forms\Components\Select::make('decision_id')
                            ->label('Décision attaquée')
                            ->options(function () {
                                return \App\Models\Decision::query()
                                    ->whereIn('statut', ['saisie', 'signee', 'enregistree', 'archivee'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(100) // Limiter pour performance
                                    ->get()
                                    ->mapWithKeys(function ($decision) {
                                        $label = $decision->numero_repertoire
                                            ?? $decision->numero_rg
                                            ?? 'Décision #' . $decision->id;

                                        $description = ' - ' . $decision->date_decision?->format('d/m/Y');

                                        if ($decision->dossier) {
                                            $description .= ' - ' . $decision->dossier->numero_dossier;
                                        }

                                        return [$decision->id => $label . $description];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Sélectionnez la décision faisant l\'objet du recours')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $decision = \App\Models\Decision::find($state);
                                    if ($decision && $decision->type_recours) {
                                        $set('type_recours', $decision->type_recours);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('numero_recours')
                            ->label('Numéro du recours')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $year = now()->year;
                                $count = Recours::whereYear('created_at', $year)->count() + 1;
                                return 'REC/' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);
                            })
                            ->maxLength(255)
                            ->helperText('Généré automatiquement, modifiable si nécessaire'),

                        Forms\Components\Select::make('type_recours_id')
                            ->label('Type de recours')
                            ->relationship('typeRecours', 'libelle', function ($query) {
                                return $query->where('is_active', true);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Type de voie de recours exercée')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $typeRecours = \App\Models\TypeRecours::find($state);
                                    if ($typeRecours) {
                                        // Copier le code dans type_recours pour compatibilité
                                        $set('type_recours', $typeRecours->code);
                                    }
                                }
                            }),

                        Forms\Components\DatePicker::make('date_recours')
                            ->label('Date du recours')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->helperText('Date de déclaration du recours'),

                        Forms\Components\TextInput::make('reference_lettre')
                            ->label('Référence de la lettre')
                            ->maxLength(255)
                            ->placeholder('Ex: LR/2024/001')
                            ->helperText('Référence de la lettre de déclaration'),

                        Forms\Components\FileUpload::make('fichier_lettre')
                            ->label('Lettre de déclaration (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->directory('recours/lettres')
                            ->helperText('Document de déclaration du recours')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Dates de traitement')
                    ->schema([
                        Forms\Components\DatePicker::make('date_enregistrement')
                            ->label('Date d\'enregistrement')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Date d\'enregistrement au greffe de la Cour'),

                        Forms\Components\DatePicker::make('date_transmission_cour_appel')
                            ->label('Date de transmission à la Cour d\'Appel')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Date d\'envoi du dossier à la Cour d\'Appel'),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Mise en état du dossier')
                    ->description('Documents de la procédure de mise en état')
                    ->schema([
                        Forms\Components\Repeater::make('documents_mise_en_etat')
                            ->label('Documents')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Type de document')
                                    ->required()
                                    ->options(function () {
                                        return \App\Models\TypeDocument::where('is_active', true)
                                            ->orderBy('libelle')
                                            ->get()
                                            ->mapWithKeys(fn($type) => [
                                                $type->code => ($type->icone ? $type->icone . ' ' : '') . $type->libelle
                                            ]);
                                    })
                                    ->searchable(),

                                Forms\Components\DatePicker::make('date')
                                    ->label('Date du document')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now()),

                                Forms\Components\TextInput::make('reference')
                                    ->label('Référence')
                                    ->maxLength(255)
                                    ->placeholder('Ex: PV/2024/001'),

                                Forms\Components\FileUpload::make('fichier')
                                    ->label('Fichier (PDF)')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->directory('recours/mise-en-etat')
                                    ->required(),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes / Observations')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('➕ Ajouter un document')
                            ->reorderable()
                            ->orderColumn('ordre')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
                    ->weight('bold')
                    ->icon('heroicon-o-scale'),

                Tables\Columns\TextColumn::make('decision.numero_repertoire')
                    ->label('Décision attaquée')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->url(fn($record) => $record->decision_id
                        ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record->decision_id])
                        : null)
                    ->tooltip('Cliquez pour voir la décision'),

                Tables\Columns\TextColumn::make('type_recours')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'appel' => '⚖️ Appel',
                        'opposition' => '⚠️ Opposition',
                        'tierce_opposition' => '👥 Tierce opposition',
                        'retractation' => '🔄 Rétractation',
                        'revision' => '🔍 Révision',
                        'pourvoi_cassation' => '⚖️ Pourvoi',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'appel' => 'danger',
                        'opposition' => 'warning',
                        'pourvoi_cassation' => 'info',
                        'tierce_opposition' => 'gray',
                        'retractation' => 'secondary',
                        'revision' => 'primary',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_recours')
                    ->label('Date recours')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('date_enregistrement')
                    ->label('Enreg.')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_transmission_cour_appel')
                    ->label('Transmission CA')
                    ->date('d/m/Y')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nombre_documents')
                    ->label('Docs')
                    ->getStateUsing(fn($record) => count($record->documents_mise_en_etat ?? []))
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-document-text')
                    ->tooltip('Nombre de documents de mise en état'),

                Tables\Columns\TextColumn::make('decision.tribunal.nom')
                    ->label('Tribunal')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_recours')
                    ->label('Type de recours')
                    ->options([
                        'appel' => 'Appel',
                        'opposition' => 'Opposition',
                        'tierce_opposition' => 'Tierce opposition',
                        'retractation' => 'Rétractation',
                        'revision' => 'Révision',
                        'pourvoi_cassation' => 'Pourvoi en cassation',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('transmis')
                    ->label('Transmis à la CA')
                    ->query(fn($query) => $query->whereNotNull('date_transmission_cour_appel')),

                Tables\Filters\Filter::make('non_transmis')
                    ->label('Non transmis')
                    ->query(fn($query) => $query->whereNull('date_transmission_cour_appel')),

                Tables\Filters\Filter::make('sans_documents')
                    ->label('Sans documents')
                    ->query(fn($query) => $query->where(function ($q) {
                        $q->whereNull('documents_mise_en_etat')
                            ->orWhereRaw("json_array_length(documents_mise_en_etat) = 0");
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('voir_decision')
                    ->label('Voir décision')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn($record) => $record->decision_id
                        ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record->decision_id])
                        : null)
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun recours enregistré')
            ->emptyStateDescription('Les recours seront listés ici une fois créés.')
            ->emptyStateIcon('heroicon-o-arrow-path-rounded-square');
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();

        if ($count === 0) {
            return 'gray';
        }

        if ($count > 10) {
            return 'danger';
        }

        if ($count > 5) {
            return 'warning';
        }

        return 'success';
    }
}