<?php

namespace App\Modules\Portal\Filament\Resources\UserResource\Pages;

use App\Modules\Portal\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $motDePasseEnClair = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // ✅ Pas de flux de vérification d'email dans cette application :
        // le compte est actif et considéré vérifié dès sa création.
        $data['email_verified_at'] = now();

        $this->motDePasseEnClair = $data['password'] ?? null;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->motDePasseEnClair) {
            Notification::make()
                ->title('✅ Compte créé avec succès')
                ->body("Email : {$this->record->email}\nMot de passe : {$this->motDePasseEnClair}\n\n⚠️ Notez-le précieusement, il ne sera plus jamais affiché en clair.")
                ->success()
                ->persistent()
                ->send();
        }
    }
}
