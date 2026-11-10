<?php

namespace App\Modules\DecisionRecours\Filament\Resources\GreffierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\GreffierResource;
use App\Support\JournalAudit;
use Filament\Resources\Pages\CreateRecord;

class CreateGreffier extends CreateRecord
{
    protected static string $resource = GreffierResource::class;

    protected function afterCreate(): void
    {
        $sections = $this->record->sections()->pluck('libelle')->sort()->values()->all();

        if ($sections !== []) {
            JournalAudit::journaliser('greffiers', 'sections_modifiees', [
                'avant' => [],
                'apres' => $sections,
            ], $this->record);
        }
    }
}
