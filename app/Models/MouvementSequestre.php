<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementSequestre extends Model
{
    protected $table = 'mouvements_sequestre';

    protected $fillable = [
        'dossier_famille_id',
        'motif_mouvement_id',
        'date_mouvement',
        'operateur_beneficiaire',
        'type_mouvement',
        'montant_mouvement',
        'taux_applique',
        'montant_precompte',
        'montant_net',
        'solde_apres',
    ];

    protected $casts = [
        'date_mouvement' => 'date',
        'montant_mouvement' => 'decimal:2',
        'taux_applique' => 'decimal:4',
        'montant_precompte' => 'decimal:2',
        'montant_net' => 'decimal:2',
        'solde_apres' => 'decimal:2',
    ];

    public function dossierFamille(): BelongsTo
    {
        return $this->belongsTo(DossierFamille::class);
    }

    public function motifMouvement(): BelongsTo
    {
        return $this->belongsTo(MotifMouvement::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type_mouvement === 'versement' ? 'Versement' : 'Retrait';
    }
}
