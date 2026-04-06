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
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations avec les sections (pour les greffiers)
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'greffier_section')
            ->withPivot(['date_affectation', 'date_fin_affectation', 'is_active'])
            ->withTimestamps();
    }

    public function sectionsActives()
    {
        return $this->sections()->wherePivot('is_active', true);
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
