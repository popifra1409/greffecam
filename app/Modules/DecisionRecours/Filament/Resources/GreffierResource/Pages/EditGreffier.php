<?php

namespace App\Modules\DecisionRecours\Filament\Resources\GreffierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\GreffierResource;
use App\Support\JournalAudit;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGreffier extends EditRecord
{
    protected static string $resource = GreffierResource::class;

    /**
     * Sections affectées avant la modification : le pivot n'est pas un
     * attribut du modèle, il doit donc être comparé explicitement.
     * Propriété publique : elle doit survivre à la requête Livewire de sauvegarde.
     */
    public array $sectionsAvant = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->sectionsAvant = $this->record->sections()
            ->pluck('libelle')
            ->sort()
            ->values()
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $sectionsApres = $this->record->sections()
            ->pluck('libelle')
            ->sort()
            ->values()
            ->all();

        if ($sectionsApres !== $this->sectionsAvant) {
            JournalAudit::journaliser('greffiers', 'sections_modifiees', [
                'avant' => $this->sectionsAvant,
                'apres' => $sectionsApres,
            ], $this->record);
        }

        $this->sectionsAvant = $sectionsApres;
    }
}
