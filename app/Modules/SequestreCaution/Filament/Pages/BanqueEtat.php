<?php

namespace App\Modules\SequestreCaution\Filament\Pages;

use App\Services\BanqueEtatService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BanqueEtat extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Banque';

    protected static ?string $navigationLabel = 'État des comptes';

    protected static ?string $title = 'État des comptes bancaires';

    protected static ?string $slug = 'banque/etat';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'sequestres.pages.banque-etat';

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exporter_pdf')
                ->label('📄 Exporter en PDF')
                ->color('primary')
                ->url(fn() => route('banque.etat.pdf', ['date_arret' => $this->dateArrete()]))
                ->openUrlInNewTab(),
        ];
    }

    protected function dateArrete(): string
    {
        $date = $this->tableFilters['date_arret']['date_arret'] ?? null;

        return $date
            ? Carbon::parse($date)->toDateString()
            : now()->toDateString();
    }

    public function table(Table $table): Table
    {
        $service = app(BanqueEtatService::class);
        $date = $this->dateArrete();

        return $table
            ->query($service->requete($date))
            ->columns([
                Tables\Columns\TextColumn::make('intitule_compte')
                    ->label('Intitulé du compte')
                    ->getStateUsing(fn($record) => $record->intitule)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('numero_compte_bancaire')
                    ->label('N° du compte')
                    ->placeholder('— (à ouvrir)')
                    ->badge()
                    ->color(fn($state) => filled($state) ? 'success' : 'warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_ouverture_compte')
                    ->label('Ouverture du compte')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_entrees')
                    ->label('Total entrées')
                    ->money('XAF')
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_sorties')
                    ->label('Total sorties')
                    ->money('XAF')
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('solde_banque_actuel')
                    ->label('Solde à la banque')
                    ->getStateUsing(fn($record) => $service->soldeBanque($record))
                    ->money('XAF')
                    ->placeholder('Non renseigné')
                    ->alignEnd()
                    ->weight('bold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('solde_sequestre_actuel')
                    ->label('Solde au séquestre')
                    ->getStateUsing(fn($record) => $service->soldeSequestre($record))
                    ->money('XAF')
                    ->alignEnd()
                    ->weight('bold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('statut_compte')
                    ->label('Statut du compte')
                    ->getStateUsing(fn($record) => $service->statutCompte($record))
                    ->badge()
                    ->color(fn($state) => $state === 'Ouvert' ? 'success' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('etat_compte')
                    ->label('État du compte')
                    ->getStateUsing(fn($record) => $service->etatCompte($record))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'À décaisser' => 'danger',
                        'À renflouer' => 'warning',
                        'Équilibré' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('montant_ecart')
                    ->label('À renflouer / décaisser')
                    ->getStateUsing(fn($record) => $service->montantEcart($record))
                    ->money('XAF')
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('interet')
                    ->label('Intérêts (2,45 %)')
                    ->getStateUsing(fn($record) => $service->interet($record, $date))
                    ->money('XAF')
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tva_interet')
                    ->label('TVA sur intérêts (19,25 %)')
                    ->getStateUsing(fn($record) => $service->tva($record, $date))
                    ->money('XAF')
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('solde_banque_interet')
                    ->label('Solde banque + intérêts')
                    ->getStateUsing(fn($record) => $service->soldeBanqueInteret($record, $date))
                    ->money('XAF')
                    ->placeholder('—')
                    ->alignEnd()
                    ->weight('bold')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_arret')
                    ->label('Date d\'arrêté')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_arret')
                            ->label('Situation au')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->helperText('Date libre : l\'état reprend les soldes arrêtés à cette date.'),
                    ])
                    ->query(fn(Builder $query) => $query)
                    ->indicateUsing(fn(array $data): array => filled($data['date_arret'] ?? null)
                        ? ['Situation au ' . Carbon::parse($data['date_arret'])->format('d/m/Y')]
                        : []),

                Tables\Filters\Filter::make('compte_ouvert')
                    ->label('Comptes ouverts uniquement')
                    ->query(fn(Builder $query) => $query->whereNotNull('numero_compte_bancaire'))
                    ->toggle(),
            ])
            ->paginated([25, 50, 100, 'all'])
            ->defaultSort('numero_dossier_sequestre')
            ->emptyStateHeading('Aucun séquestre')
            ->emptyStateDescription('Aucun séquestre ne correspond à cette situation.');
    }
}
