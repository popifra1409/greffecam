<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Greffier extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tribunal_id',
        'matricule',
        'nom',
        'prenom',
        'titre',
        'grade',
        'email',
        'telephone',
        'est_chef',
        'is_active',
    ];

    protected $casts = [
        'est_chef' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['matricule', 'nom', 'prenom', 'grade', 'est_chef', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations
    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'greffier_section')
            ->withTimestamps();
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class, 'greffier');
    }

    public function dossiers()
    {
        return $this->hasMany(Dossier::class, 'enrole_par');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // Accesseurs
    public function getNomCompletAttribute()
    {
        return trim(($this->titre ? $this->titre . ' ' : '') . $this->nom . ' ' . $this->prenom);
    }

    public function getFonctionAttribute()
    {
        return $this->est_chef ? 'Greffier en Chef' : 'Greffier';
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeChefs($query)
    {
        return $query->where('est_chef', true);
    }

    public function scopeDuTribunal($query, $tribunalId)
    {
        return $query->where('tribunal_id', $tribunalId);
    }

    public function scopeDeLaSection($query, $sectionId)
    {
        return $query->whereHas('sections', function ($q) use ($sectionId) {
            $q->where('sections.id', $sectionId);
        });
    }
}
