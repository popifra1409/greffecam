<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSequestre extends CreateRecord
{
    protected static string $resource = SequestreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
