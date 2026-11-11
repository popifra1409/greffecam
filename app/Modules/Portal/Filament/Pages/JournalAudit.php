<?php

namespace App\Modules\Portal\Filament\Pages;

use App\Filament\Filters\PeriodeFilter;
use App\Models\User;
use App\Support\JournalAudit as JournalAuditSupport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class JournalAudit extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Traçabilité';

    protected static ?string $navigationLabel = 'Journal d\'audit';

    protected static ?string $title = 'Journal d\'audit';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'modules.portal.audit.journal';

    public static function canAccess(): bool
    {
        return JournalAuditSupport::peutConsulter(auth()->user());
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('archives')
                ->label('📦 Archives')
                ->color('gray')
                ->url(fn(): string => ArchivesJournalAudit::getUrl()),

            Action::make('archiver')
                ->label('🗄️ Archiver maintenant')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Archiver le journal d\'audit')
                ->modalDescription(fn(): string => sprintf(
                    'Les entrées de plus de %d mois seront déplacées vers une archive compressée, puis supprimées du journal actif.',
                    (int) config('audit.retention_mois', 6)
                ))
                ->modalSubmitActionLabel('Lancer l\'archivage')
                ->action(function (): void {
                    try {
                        Artisan::call('audit:archiver');
                        $sortie = trim(Artisan::output());

                        Notification::make()
                            ->title('Archivage terminé')
                            ->body(Str::limit($sortie, 500))
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('Archivage impossible')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function getStatistiques(): array
    {
        return [
            'total' => Activity::query()->count(),
            'plus_ancienne' => Activity::query()->min('created_at'),
            'retention' => (int) config('audit.retention_mois', 6),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->with(['causer', 'subject']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Auteur')
                    ->placeholder('Système')
                    ->searchable(),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('Journal')
                    ->formatStateUsing(fn(?string $state): string => JournalAuditSupport::libelleJournal($state))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->formatStateUsing(fn(?string $state): string => JournalAuditSupport::libelleEvenement($state))
                    ->badge()
                    ->color(fn(?string $state): string => JournalAuditSupport::couleurEvenement($state)),

                Tables\Columns\TextColumn::make('sujet')
                    ->label('Élément concerné')
                    ->getStateUsing(fn(Activity $record): string => JournalAuditSupport::libelleSujet($record))
                    ->wrap(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->formatStateUsing(fn(?string $state): string => in_array($state, ['created', 'updated', 'deleted', 'restored'], true)
                        ? JournalAuditSupport::libelleEvenement($state)
                        : (string) $state)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                PeriodeFilter::make('created_at', 'Période', Activity::class),

                Tables\Filters\SelectFilter::make('event')
                    ->label('Action')
                    ->options(fn(): array => Activity::query()
                        ->whereNotNull('event')
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->mapWithKeys(fn($evenement, $cle) => [$cle => JournalAuditSupport::libelleEvenement($evenement)])
                        ->all()),

                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Journal')
                    ->options(fn(): array => Activity::query()
                        ->whereNotNull('log_name')
                        ->distinct()
                        ->orderBy('log_name')
                        ->pluck('log_name', 'log_name')
                        ->mapWithKeys(fn($journal, $cle) => [$cle => JournalAuditSupport::libelleJournal($journal)])
                        ->all()),

                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('Auteur')
                    ->options(fn(): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Détails')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Détail de l\'entrée')
                    ->modalContent(fn(Activity $record) => view('modules.portal.audit.details', [
                        'activite' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer'),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->emptyStateHeading('Aucune entrée')
            ->emptyStateDescription('Le journal se remplit automatiquement à chaque action enregistrée dans l\'application.')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50);
    }
}
