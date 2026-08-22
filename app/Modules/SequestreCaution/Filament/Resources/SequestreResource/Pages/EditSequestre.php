<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSequestre extends EditRecord
{
    protected static string $resource = SequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
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
