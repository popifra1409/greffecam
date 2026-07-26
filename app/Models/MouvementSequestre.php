<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementSequestre extends Model
{
    protected $table = 'mouvements_sequestre';

    protected $fillable = [
        'sequestre_id',
        'motif_mouvement_id',
        'sequestre_partie_adverse_id',
        'sequestre_ayant_droit_id',
        'est_procuration',
        'mandataire_nom',
        'mandataire_reference_procuration',
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
        'est_procuration' => 'boolean',
    ];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }

    public function motifMouvement(): BelongsTo
    {
        return $this->belongsTo(MotifMouvement::class);
    }

    public function partieAdverse(): BelongsTo
    {
        return $this->belongsTo(SequestrePartieAdverse::class, 'sequestre_partie_adverse_id');
    }

    public function ayantDroit(): BelongsTo
    {
        return $this->belongsTo(SequestreAyantDroit::class, 'sequestre_ayant_droit_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SequestreDocument::class);
    }

    public function getDechargeJointeAttribute(): bool
    {
        return $this->documents()->where('categorie', 'quittance')->exists();
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type_mouvement === 'versement' ? 'Versement' : 'Retrait';
    }

    /**
     * Nom à afficher pour ce mouvement : celui du mandataire si procuration,
     * sinon celui de la partie adverse (versement) ou de l'ayant droit (retrait).
     */
    public function getNomAffichageAttribute(): string
    {
        if ($this->est_procuration && $this->mandataire_nom) {
            return $this->mandataire_nom . ' (mandataire)';
        }

        return $this->operateur_beneficiaire ?? '—';
    }
}
