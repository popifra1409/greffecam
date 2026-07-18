<?php
// ListSequestres.php
namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSequestres extends ListRecords
{
    protected static string $resource = SequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
