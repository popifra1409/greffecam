<?php
// EditSequestre.php
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
}
