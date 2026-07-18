<?php
// ViewSequestre.php
namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSequestre extends ViewRecord
{
    protected static string $resource = SequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Séquestre')
                ->schema([
                    Infolists\Components\TextEntry::make('dossier.numero_dossier')->label('N° Dossier')->badge(),
                    Infolists\Components\TextEntry::make('intitule')->label('Intitulé'),
                    Infolists\Components\TextEntry::make('decision.numero_repertoire')->label('N° Décision')->badge(),
                    Infolists\Components\TextEntry::make('natureSequestre.libelle')->label('Nature')->badge(),
                    Infolists\Components\TextEntry::make('statutSequestre.libelle')->label('Statut')->badge(),
                    Infolists\Components\TextEntry::make('date_ouverture')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('taux_pourcentage')->label('Taux de précompte'),
                    Infolists\Components\TextEntry::make('solde_actuel')->label('Solde actuel')->money('XAF')->weight('bold'),
                ])->columns(2),
        ]);
    }
}
