<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierPartie extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'type_partie',
        'est_personne_morale',
        'est_famille',
        'nom_famille',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'profession',
        'nationalite',
        'raison_sociale',
        'representant_legal',
        'adresse',
        'telephone',
        'email',
        'avocat_nom',
        'avocat_contact',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'est_personne_morale' => 'boolean',
        'est_famille' => 'boolean',
    ];

    // Relations
    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    // Accesseurs
    public function getNomCompletAttribute()
    {
        if ($this->est_personne_morale) {
            return $this->raison_sociale;
        }
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getTypeLabelAttribute()
    {
        return match ($this->type_partie) {
            'demandeur' => 'Demandeur',
            'defendeur' => 'Défendeur',
            'partie_civile' => 'Partie Civile',
            'prevenu' => 'Prévenu',
            'temoin' => 'Témoin',
            default => $this->type_partie,
        };
    }

    public function getLabelFamilleAttribute(): ?string
    {
        return $this->est_famille ? "Famille {$this->nom_famille}" : null;
    }
}
