<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AnneeJudiciaire extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'is_active',
        'is_cloturee',
        'observations',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'is_active' => 'boolean',
        'is_cloturee' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['libelle', 'is_active', 'is_cloturee'])
            ->logOnlyDirty();
    }

    // Relations
    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNonCloturees($query)
    {
        return $query->where('is_cloturee', false);
    }

    // Helper pour activer une année (désactive les autres)
    public function activer()
    {
        // Désactiver toutes les autres années
        static::where('id', '!=', $this->id)->update(['is_active' => false]);

        // Activer celle-ci
        $this->update(['is_active' => true]);
    }
}
