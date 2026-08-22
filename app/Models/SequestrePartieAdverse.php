<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SequestrePartieAdverse extends Model
{
    protected $table = 'sequestre_parties_adverses';

    protected $fillable = [
        'sequestre_id',
        'nom_complet',
        'numero_cni',
        'telephone',
        'adresse',
        'montant_loyer_attendu',
        'jour_echeance',
        'observations',
    ];

    protected $casts = [
        'montant_loyer_attendu' => 'decimal:2',
        'jour_echeance' => 'integer',
    ];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }

    /**
     * Mouvements (versements) effectués par cette partie adverse.
     */
    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementSequestre::class, 'sequestre_partie_adverse_id');
    }

    /**
     * Total versé par cette partie adverse durant le mois en cours.
     */
    public function getTotalVerseMoisAttribute(): float
    {
        return (float) $this->mouvements()
            ->where('type_mouvement', 'versement')
            ->whereBetween('date_mouvement', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('montant_mouvement');
    }

    /**
     * Statut du versement du mois en cours par rapport au loyer attendu :
     * - 'aucun_attendu' : pas de loyer configuré pour cette partie adverse
     * - 'aucun'         : rien versé ce mois, et échéance pas encore dépassée
     * - 'en_retard'      : rien versé ce mois, et échéance dépassée
     * - 'partiel'       : versé, mais moins que le montant attendu
     * - 'complet'       : versé au moins le montant attendu
     */
    public function getStatutVersementMoisAttribute(): string
    {
        if (empty($this->montant_loyer_attendu)) {
            return 'aucun_attendu';
        }

        $totalVerseMois = $this->total_verse_mois;

        $dateEcheance = $this->jour_echeance
            ? now()->startOfMonth()->addDays($this->jour_echeance - 1)
            : null;

        if ($totalVerseMois <= 0) {
            if ($dateEcheance && now()->greaterThan($dateEcheance)) {
                return 'en_retard';
            }
            return 'aucun';
        }

        if ($totalVerseMois < $this->montant_loyer_attendu) {
            return 'partiel';
        }

        return 'complet';
    }

    public function getStatutVersementLabelAttribute(): string
    {
        return match ($this->statut_versement_mois) {
            'aucun_attendu' => 'Sans loyer défini',
            'aucun' => 'Rien versé ce mois',
            'en_retard' => 'En retard',
            'partiel' => 'Versement partiel',
            'complet' => 'À jour',
            default => 'Inconnu',
        };
    }

    public function getStatutVersementCouleurAttribute(): string
    {
        return match ($this->statut_versement_mois) {
            'complet' => 'success',
            'partiel' => 'warning',
            'en_retard' => 'danger',
            'aucun' => 'gray',
            default => 'gray',
        };
    }
}
