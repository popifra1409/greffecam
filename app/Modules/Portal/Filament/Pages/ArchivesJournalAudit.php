<?php

namespace App\Modules\Portal\Filament\Pages;

use App\Models\AuditArchive;
use App\Support\JournalAudit as JournalAuditSupport;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ArchivesJournalAudit extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Traçabilité';

    protected static ?string $navigationLabel = 'Archives du journal';

    protected static ?string $title = 'Archives du journal d\'audit';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'modules.portal.audit.archives';

    public static function canAccess(): bool
    {
        return JournalAuditSupport::peutConsulter(auth()->user());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditArchive::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('periode_debut')
                    ->label('Du')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode_fin')
                    ->label('Au')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre_entrees')
                    ->label('Entrées')
                    ->numeric(decimalPlaces: 0)
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('taille_lisible')
                    ->label('Taille')
                    ->badge()
                    ->color('gray')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('nom_fichier')
                    ->label('Fichier')
                    ->copyable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Archivé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('telecharger')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(AuditArchive $record): string => route('audit.archives.telecharger', $record))
                    ->openUrlInNewTab(),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->emptyStateHeading('Aucune archive')
            ->emptyStateDescription('Les archives sont créées automatiquement le 1er de chaque mois, ou manuellement depuis le journal d\'audit.')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
