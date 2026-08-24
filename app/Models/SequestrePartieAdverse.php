<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\EcheancierService;

class SequestrePartieAdverse extends Model
{
    protected $table = 'sequestre_parties_adverses';

    protected $fillable = [
        'sequestre_id',
        'nom_complet',
        'numero_cni',
        'telephone',
        'adresse',
        'date_debut_paiement',
        'montant_echeance',
        'periodicite',
        'duree_contrat_mois',
        'observations',
    ];

    protected $casts = [
        'date_debut_paiement' => 'date',
        'montant_echeance' => 'decimal:2',
        'duree_contrat_mois' => 'integer',
    ];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementSequestre::class, 'sequestre_partie_adverse_id');
    }

    /**
     * Échéancier complet (une ligne par échéance), calculé à la volée.
     */
    public function getEcheancierAttribute(): \Illuminate\Support\Collection
    {
        return app(EcheancierService::class)->genererEcheancier($this);
    }

    /**
     * Résumé rapide de la situation actuelle.
     */
    public function getSituationActuelleAttribute(): array
    {
        return app(EcheancierService::class)->situationActuelle($this);
    }

    public function getStatutVersementLabelAttribute(): string
    {
        return $this->situation_actuelle['statut_label'];
    }

    public function getStatutVersementCouleurAttribute(): string
    {
        return $this->situation_actuelle['statut_couleur'];
    }

    public function getResteAPayerAttribute(): float
    {
        return $this->situation_actuelle['reste_a_payer'];
    }

    public function getPeriodiciteLabelAttribute(): string
    {
        return match ($this->periodicite) {
            'mensuel' => 'Mensuel',
            'trimestriel' => 'Trimestriel',
            'semestriel' => 'Semestriel',
            'annuel' => 'Annuel',
            default => $this->periodicite,
        };
    }
}
