<?php

namespace App\Models;

use App\Traits\JournaliseActivite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementSequestre extends Model
{
    use JournaliseActivite;

    protected $table = 'mouvements_sequestre';

    protected $fillable = [
        'sequestre_id',
        'motif_mouvement_id',
        'sequestre_partie_adverse_id',
        'sequestre_ayant_droit_id',
        'sequestre_partie_tierce_id',
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

    public function partieTierce(): BelongsTo
    {
        return $this->belongsTo(SequestrePartieTierce::class, 'sequestre_partie_tierce_id');
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
     * Type de bénéficiaire renseigné à la saisie :
     * - versement : le locataire (partie adverse) qui paie ;
     * - retrait   : l'ayant droit (avec son rôle) ou la partie tierce (avec son type).
     * Vaut null quand aucun bénéficiaire n'a été renseigné.
     */
    public function getTypeBeneficiaireAttribute(): ?string
    {
        if ($this->type_mouvement === 'versement') {
            return $this->sequestre_partie_adverse_id ? 'Locataire' : null;
        }

        if ($this->sequestre_partie_tierce_id) {
            $type = $this->partieTierce?->type_label;

            return $type ? 'Partie tierce : ' . $type : 'Partie tierce';
        }

        if ($this->sequestre_ayant_droit_id) {
            $role = $this->ayantDroit?->role_succession;

            return $role
                ? 'Ayant droit (' . $this->ayantDroit->role_succession_label . ')'
                : 'Ayant droit';
        }

        return null;
    }

    /**
     * Nom à afficher pour ce mouvement :
     * - le mandataire si retrait par procuration (ayant droit)
     * - la partie tierce si retrait attribué à un huissier/avocat/service public
     * - sinon operateur_beneficiaire (déjà calculé lors de la saisie)
     */
    public function getNomAffichageAttribute(): string
    {
        if ($this->sequestre_partie_tierce_id && $this->partieTierce) {
            return $this->partieTierce->nom_complet . ' (' . $this->partieTierce->type_label . ')';
        }

        if ($this->est_procuration && $this->mandataire_nom) {
            return $this->mandataire_nom . ' (mandataire)';
        }

        return $this->operateur_beneficiaire ?? '—';
    }
}
