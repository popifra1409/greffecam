<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSequestre extends EditRecord
{
    protected static string $resource = SequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    /**
     * ✅ Sur la page d'édition, seuls Mouvements et Documents apparaissent en
     * onglets de bas de page : les Ayants droit / Parties adverses / Partie
     * Tierce restent gérés via les onglets Repeater du formulaire ci-dessus,
     * pour éviter d'avoir deux façons différentes de modifier la même donnée
     * sur le même écran.
     */
    public function getRelationManagers(): array
    {
        return [
            RelationManagers\MouvementsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->filtrerLignesVides($data);
    }

    protected function filtrerLignesVides(array $data): array
    {
        foreach (['ayantsDroit', 'partiesAdverses', 'partiesTierces'] as $groupe) {
            $data[$groupe] = collect($data[$groupe] ?? [])
                ->filter(function ($item) {
                    return collect($item)->filter(fn($valeur) => filled($valeur))->isNotEmpty();
                })
                ->values()
                ->toArray();
        }

        return $data;
    }
}
