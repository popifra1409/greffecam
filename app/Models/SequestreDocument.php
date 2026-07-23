<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequestreDocument extends Model
{
    protected $fillable = [
        'sequestre_id',
        'categorie',
        'libelle',
        'fichier_path',
        'fichier_nom_original',
        'description',
        'depose_par',
    ];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }

    public function deposePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'depose_par');
    }

    public function getCategorieLabelAttribute(): string
    {
        return match ($this->categorie) {
            'courrier' => 'Courrier',
            'procedure' => 'Procédure',
            'contrat' => 'Contrat',
            'quittance' => 'Quittance',
            default => $this->categorie,
        };
    }
}
