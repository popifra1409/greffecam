<?php

namespace App\Observers;

use App\Models\User;
use App\Support\JournalAudit;

class UserObserver
{
    /**
     * Le changement de mot de passe n'est jamais journalisé par les attributs
     * (le hash serait exposé) : on enregistre uniquement le fait, sans valeur.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('password')) {
            JournalAudit::journaliser('utilisateurs', 'password_updated', [], $user);
        }
    }
}
