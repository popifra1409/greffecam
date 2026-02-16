<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransmissionDecisionResource\Pages;
use App\Models\TransmissionDecision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransmissionDecisionResource extends Resource
{
    protected static ?string $model = TransmissionDecision::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Gestion Judiciaire';

    protected static ?string $modelLabel = 'Transmission';

    protected static ?string $pluralModelLabel = 'Historique des transmissions';

    protected static ?int $navigationSort = 14;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('destinataire_id', auth()->id())
            ->where('statut', 'en_attente')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('destinataire_id', auth()->id())
            ->where('statut', 'en_attente')
            ->count();

        if ($count > 5) {
            return 'danger';
        } elseif ($count > 2) {
            return 'warning';
        }

        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la transmission')
                    ->schema([
                        Forms\Components\Select::make('decision_id')
                            ->label('Décision')
                            ->relationship('decision', 'numero_rg')
                            ->disabled(),

                        Forms\Components\Select::make('expediteur_id')
                            ->label('Expéditeur')
                            ->relationship('expediteur', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('destinataire_id')
                            ->label('Destinataire')
                            ->relationship('destinataire', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('motif')
                            ->label('Motif')
                            ->options([
                                'validation' => 'Validation',
                                'signature' => 'Signature',
                                'correction' => 'Correction',
                                'avis' => 'Avis',
                                'information' => 'Information',
                                'autre' => 'Autre',
                            ])
                            ->disabled(),

                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'en_attente' => 'En attente',
                                'acceptee' => 'Acceptée',
                                'rejetee' => 'Rejetée',
                                'retournee' => 'Retournée',
                            ])
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('date_transmission')
                            ->label('Date de transmission')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('date_traitement')
                            ->label('Date de traitement')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Observations')
                    ->schema([
                        Forms\Components\Textarea::make('observations_expediteur')
                            ->label('Observations de l\'expéditeur')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observations_destinataire')
                            ->label('Observations du destinataire')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();

                // Si admin ou greffier en chef, voir tout
                if ($user->hasAnyRole(['Administrateur', 'Greffier en Chef'])) {
                    return $query;
                }

                // Sinon, voir uniquement les transmissions dont on est expéditeur ou destinataire
                return $query->where(function ($q) use ($user) {
                    $q->where('expediteur_id', $user->id)
                        ->orWhere('destinataire_id', $user->id);
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('decision.numero_rg')
                    ->label('Décision')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.decisions.view', $record->decision_id))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('expediteur.name')
                    ->label('Expéditeur')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('destinataire.name')
                    ->label('Destinataire')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->color(fn($record) => $record->destinataire_id === auth()->id() ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('motif')
                    ->label('Motif')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'validation' => 'info',
                        'signature' => 'success',
                        'correction' => 'warning',
                        'avis' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'validation' => 'Validation',
                        'signature' => 'Signature',
                        'correction' => 'Correction',
                        'avis' => 'Avis',
                        'information' => 'Information',
                        'autre' => 'Autre',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'en_attente' => 'warning',
                        'acceptee' => 'success',
                        'rejetee' => 'danger',
                        'retournee' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'acceptee' => 'Acceptée',
                        'rejetee' => 'Rejetée',
                        'retournee' => 'Retournée',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_transmission')
                    ->label('Transmise le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_traitement')
                    ->label('Traitée le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non traitée')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'acceptee' => 'Acceptée',
                        'rejetee' => 'Rejetée',
                        'retournee' => 'Retournée',
                    ]),

                Tables\Filters\SelectFilter::make('motif')
                    ->label('Motif')
                    ->options([
                        'validation' => 'Validation',
                        'signature' => 'Signature',
                        'correction' => 'Correction',
                        'avis' => 'Avis',
                        'information' => 'Information',
                        'autre' => 'Autre',
                    ]),

                Tables\Filters\Filter::make('mes_transmissions')
                    ->label('Mes transmissions envoyées')
                    ->query(fn($query) => $query->where('expediteur_id', auth()->id())),

                Tables\Filters\Filter::make('transmissions_recues')
                    ->label('Transmissions reçues')
                    ->query(fn($query) => $query->where('destinataire_id', auth()->id())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir'),
            ])
            ->defaultSort('date_transmission', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransmissionDecisions::route('/'),
            'view' => Pages\ViewTransmissionDecision::route('/{record}'),
        ];
    }
}
