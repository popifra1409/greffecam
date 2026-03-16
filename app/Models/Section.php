<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Section extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'libelle',
        'code',
        'type',
        'description',
        'utilise_assesseur',
        'is_active',
    ];

    protected $casts = [
        'utilise_assesseur' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['libelle', 'code', 'type', 'is_active'])
            ->logOnlyDirty();
    }

    // Relations
    public function matieres()
    {
        return $this->hasMany(Matiere::class);
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    public function infractions()
    {
        return $this->hasMany(Infraction::class);
    }

    public function dossiers()
    {
        return $this->hasMany(Dossier::class);
    }

    public function greffiers()
    {
        return $this->belongsToMany(Greffier::class, 'greffier_section')
            ->withTimestamps();
    }

    // Helpers
    public function estRepressive(): bool
    {
        return $this->type === 'repressive';
    }

    public function estNonRepressive(): bool
    {
        return $this->type === 'non_repressive';
    }

    // Obtenir les types de parties selon le type de section
    public function getTypesPartiesAttribute(): array
    {
        if ($this->type === 'repressive') {
            return [
                'ministere_public' => 'Ministère Public',
                'partie_civile' => 'Partie Civile',
                'prevenu' => 'Prévenu',
                'temoin' => 'Témoin',
            ];
        }

        // Non répressive
        return [
            'demandeur' => 'Demandeur',
            'defendeur' => 'Défendeur',
            'temoin' => 'Témoin',
        ];
    }
}
