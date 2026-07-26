<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sequestre extends Model
{
    protected $fillable = [
        'numero_dossier_sequestre',
        'decision_id',
        'dossier_id',
        'dossier_partie_id',
        'nature_sequestre_id',
        'statut_sequestre_id',
        'date_ouverture',
        'taux_precompte',
        'observations',
    ];

    protected $casts = [
        'date_ouverture' => 'date',
        'taux_precompte' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (Sequestre $sequestre) {
            if ($sequestre->decision_id) {
                $decision = $sequestre->relationLoaded('decision')
                    ? $sequestre->decision
                    : Decision::with('dossier.tribunal')->find($sequestre->decision_id);

                if ($decision) {
                    $sequestre->dossier_id = $decision->dossier_id;
                }
            }
        });

        static::creating(function (Sequestre $sequestre) {
            if (empty($sequestre->numero_dossier_sequestre)) {
                $sequestre->numero_dossier_sequestre = static::genererNumeroDossierSequestre($sequestre);
            }
        });
    }

    /**
     * Format : TPI-YDCA/SEQ/26/000001
     * (sigle tribunal / SEQ / année 2 chiffres / compteur 6 chiffres)
     */
    public static function genererNumeroDossierSequestre(Sequestre $sequestre): string
    {
        $decision = Decision::with('dossier.tribunal')->find($sequestre->decision_id);
        $tribunal = $decision?->dossier?->tribunal;

        $sigleTribunal = $tribunal?->sigle ? strtoupper($tribunal->sigle) : 'TPI';
        $annee = now()->format('y');

        $compteur = static::where('numero_dossier_sequestre', 'like', "{$sigleTribunal}/SEQ/{$annee}/%")
            ->count() + 1;

        return sprintf('%s/SEQ/%s/%06d', $sigleTribunal, $annee, $compteur);
    }

    // ================================================================
    // RELATIONS
    // ================================================================

    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function representant(): BelongsTo
    {
        return $this->belongsTo(DossierPartie::class, 'dossier_partie_id');
    }

    public function natureSequestre(): BelongsTo
    {
        return $this->belongsTo(NatureSequestre::class);
    }

    public function statutSequestre(): BelongsTo
    {
        return $this->belongsTo(StatutSequestre::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementSequestre::class)->orderBy('date_mouvement')->orderBy('id');
    }

    public function ayantsDroit(): HasMany
    {
        return $this->hasMany(SequestreAyantDroit::class);
    }

    public function partiesAdverses(): HasMany
    {
        return $this->hasMany(SequestrePartieAdverse::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SequestreDocument::class);
    }

    // ================================================================
    // ACCESSEURS
    // ================================================================

    public function getIntituleAttribute(): string
    {
        return $this->dossier?->label_dossier_sequestre
            ?? $this->representant?->nom_complet
            ?? $this->dossier?->demandeur_nom_complet
            ?? $this->dossier?->numero_dossier
            ?? '—';
    }

    public function getNumeroDossierAttribute(): ?string
    {
        return $this->dossier?->numero_dossier;
    }

    public function getNumeroDecisionAttribute(): ?string
    {
        return $this->decision?->numero_repertoire;
    }

    public function getTypeDecisionLabelAttribute(): ?string
    {
        return $this->decision?->typeDecision?->libelle;
    }

    public function getNatureDecisionLabelAttribute(): ?string
    {
        return $this->decision?->natureDecision?->libelle;
    }

    public function getDateDecisionAttribute(): ?\Carbon\Carbon
    {
        return $this->decision?->date_decision;
    }

    public function getTotalEntreesAttribute(): float
    {
        return (float) $this->mouvements()->where('type_mouvement', 'versement')->sum('montant_mouvement');
    }

    public function getTotalSortiesAttribute(): float
    {
        return (float) $this->mouvements()->where('type_mouvement', 'retrait')->sum('montant_mouvement');
    }

    public function getMontantSequestreTotalAttribute(): float
    {
        return (float) $this->mouvements()->sum('montant_precompte');
    }
    public function getSoldeActuelAttribute(): float
    {
        // ✅ reorder() efface le tri par défaut hérité de la relation mouvements(),
        // avant d'appliquer le tri décroissant voulu ici.
        $dernierMouvement = $this->mouvements()
            ->reorder()
            ->orderByDesc('date_mouvement')
            ->orderByDesc('id')
            ->first();

        return $dernierMouvement ? (float) $dernierMouvement->solde_apres : 0.0;
    }

    public function getTauxPourcentageAttribute(): string
    {
        return number_format($this->taux_precompte * 100, 2) . ' %';
    }
}
