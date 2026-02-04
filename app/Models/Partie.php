<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    use HasFactory;

    protected $fillable = [
        'decision_id',
        'type',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'profession',
        'nationalite',
        'adresse',
        'telephone',
        'email',
        'is_personne_morale',
        'raison_sociale',
        'representant_legal',
        'avocat_nom',
        'avocat_contact',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'is_personne_morale' => 'boolean',
    ];

    // Relations
    public function decision()
    {
        return $this->belongsTo(Decision::class);
    }

    // Accesseurs
    public function getNomCompletAttribute()
    {
        if ($this->is_personne_morale) {
            return $this->raison_sociale;
        }
        return trim($this->nom . ' ' . $this->prenom);
    }
}
