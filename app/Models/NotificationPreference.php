<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_enabled',
        'sms_enabled',
        'whatsapp_enabled',
        'push_enabled',
        'phone_number',
        'whatsapp_number',
        'frequence',
        'heure_debut',
        'heure_fin',
        'recours_non_enregistres',
        'recours_non_transmis',
        'recours_urgents',
        'resume_quotidien',
        'heure_resume',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'recours_non_enregistres' => 'boolean',
        'recours_non_transmis' => 'boolean',
        'recours_urgents' => 'boolean',
        'resume_quotidien' => 'boolean',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime',
        'heure_resume' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les canaux actifs
     */
    public function getCanauxActifs(): array
    {
        $canaux = [];

        if ($this->push_enabled) {
            $canaux[] = 'database';
        }

        if ($this->email_enabled) {
            $canaux[] = 'mail';
        }

        if ($this->sms_enabled && $this->phone_number) {
            $canaux[] = 'sms';
        }

        if ($this->whatsapp_enabled && $this->whatsapp_number) {
            $canaux[] = 'whatsapp';
        }

        return $canaux;
    }

    /**
     * Vérifier si on est dans les heures d'envoi
     */
    public function estDansHeuresEnvoi(): bool
    {
        $now = now()->format('H:i');
        $debut = $this->heure_debut->format('H:i');
        $fin = $this->heure_fin->format('H:i');

        return $now >= $debut && $now <= $fin;
    }

    /**
     * Obtenir les canaux selon l'urgence (respect des heures)
     */
    public function getCanauxPourUrgence(string $niveau): array
    {
        $canaux = [];

        // URGENT : toujours notifier (même hors heures)
        if ($niveau === 'urgent') {
            return $this->getCanauxActifs();
        }

        // Normal : respecter les heures
        if (!$this->estDansHeuresEnvoi()) {
            // Hors heures : seulement database/email (pas SMS/WhatsApp)
            if ($this->push_enabled)
                $canaux[] = 'database';
            if ($this->email_enabled)
                $canaux[] = 'mail';
            return $canaux;
        }

        // Dans les heures : tous les canaux actifs
        return $this->getCanauxActifs();
    }
}