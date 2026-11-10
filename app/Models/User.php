<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'greffier_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasVerifiedEmail();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active', 'greffier_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('utilisateurs')
            ->setDescriptionForEvent(fn(string $event) => \App\Support\JournalAudit::libelleEvenement($event));
    }

    /**
     * Fiche greffier rattachée au compte (porte les affectations de sections).
     */
    public function greffier()
    {
        return $this->belongsTo(Greffier::class);
    }

    /**
     * Sections du greffier rattaché. Les affectations sont portées par la
     * fiche greffier (table greffier_section), pas par le compte utilisateur.
     */
    public function sections()
    {
        if ($this->greffier) {
            return $this->greffier->sections();
        }

        return Section::query()->whereRaw('1 = 0');
    }

    public function sectionsActives()
    {
        return $this->sections()->where('sections.is_active', true);
    }

    /**
     * Un greffier (hors greffier en chef) ne voit que sa section et ses
     * dossiers affectés. Les autres rôles conservent l'accès global.
     */
    public function estRestreintAuxSections(): bool
    {
        return $this->hasRole('Greffier')
            && ! $this->hasAnyRole(['Super Administrateur', 'Administrateur', 'Greffier en Chef']);
    }

    /**
     * Identifiants des sections ouvertes au compte (via le greffier rattaché).
     */
    public function sectionsAutorisees(): \Illuminate\Support\Collection
    {
        $sections = $this->greffier?->sections;

        if (! $sections) {
            return collect();
        }

        return $sections
            ->where('is_active', true)
            ->pluck('id')
            ->values();
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Route notification pour SMS (Twilio)
     */
    public function routeNotificationForTwilio(): ?string
    {
        return $this->notificationPreference?->phone_number;
    }

    /**
     * Route notification pour WhatsApp (Twilio)
     */
    public function routeNotificationForWhatsApp(): ?string
    {
        $number = $this->notificationPreference?->whatsapp_number;
        return $number ? "whatsapp:{$number}" : null;
    }

    /**
     * Créer les préférences par défaut si inexistantes
     */
    public function getOrCreateNotificationPreference(): NotificationPreference
    {
        if (!$this->notificationPreference) {
            return $this->notificationPreference()->create([
                'email_enabled' => true,
                'push_enabled' => true,
            ]);
        }

        return $this->notificationPreference;
    }

    private function getUtilisateursResumeQuotidien(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('notificationPreference', function ($query) {
            $query->where('resume_quotidien', true);
        })
            ->with('notificationPreference')
            ->get();
    }
}
