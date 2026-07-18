<?php
// CreateSequestre.php
namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSequestre extends CreateRecord
{
    protected static string $resource = SequestreResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
