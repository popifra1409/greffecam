<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Services\DelaiCalculator;
use Carbon\Carbon;

class Recours extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'recours';

    protected $fillable = [
        'numero_recours',
        'decision_id',
        'type_recours_id',
        'annee_judiciaire_id',
        'appelant',
        'intime',
        'date_decision_attaquee',
        'date_interjetee',
        'date_limite_recours',
        'date_notification',
        'statut_recevabilite',
        'motif_irrecevabilite',
        'date_decision_recevabilite',
        'etape_actuelle',
        'statut_global',
        'observations',
        'greffier_responsable_id',
        'is_archived',
    ];

    protected $casts = [
        'date_decision_attaquee' => 'date',
        'date_interjetee' => 'date',
        'date_limite_recours' => 'date',
        'date_notification' => 'date',
        'date_decision_recevabilite' => 'date',
        'etape_actuelle' => 'integer',
        'is_archived' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations
    public function decision()
    {
        return $this->belongsTo(Decision::class);
    }

    public function typeRecours()
    {
        return $this->belongsTo(TypeRecours::class);
    }

    public function anneeJudiciaire()
    {
        return $this->belongsTo(AnneeJudiciaire::class);
    }

    public function etapes()
    {
        return $this->hasMany(EtapeRecours::class)->orderBy('numero_etape');
    }

    public function actes()
    {
        return $this->hasMany(ActeRecours::class);
    }

    public function alertes()
    {
        return $this->hasMany(AlerteRecours::class);
    }

    public function greffierResponsable()
    {
        return $this->belongsTo(User::class, 'greffier_responsable_id');
    }

    // Helpers pour le calcul des délais
    public function calculerDateLimite(): void
    {
        if ($this->date_decision_attaquee && $this->typeRecours) {
            $delaiJours = $this->typeRecours->delai_jours;
            $dateLimite = DelaiCalculator::calculerDateLimite(
                Carbon::parse($this->date_decision_attaquee),
                $delaiJours
            );

            $this->date_limite_recours = $dateLimite;
            $this->save();
        }
    }

    public function getJoursRestantsAttribute(): int
    {
        if (!$this->date_limite_recours) {
            return 0;
        }

        return DelaiCalculator::calculerJoursRestants(
            Carbon::parse($this->date_limite_recours)
        );
    }

    public function getNiveauAlerteAttribute(): ?string
    {
        return DelaiCalculator::determinerNiveauAlerte($this->jours_restants);
    }

    public function estRecevable(): bool
    {
        if (!$this->date_interjetee || !$this->date_limite_recours) {
            return false;
        }

        return Carbon::parse($this->date_interjetee)->lessThanOrEqualTo(
            Carbon::parse($this->date_limite_recours)
        );
    }

    public function marquerRecevabilite(): void
    {
        $this->statut_recevabilite = $this->estRecevable() ? 'recevable' : 'irrecevable';

        if (!$this->estRecevable()) {
            $this->motif_irrecevabilite = 'Recours interjeté hors délai légal';
        }

        $this->date_decision_recevabilite = now();
        $this->save();
    }

    // Initialiser les 11 étapes du workflow
    public function initialiserEtapes(): void
    {
        $etapes = [
            1 => 'Dépôt du recours',
            2 => 'Enregistrement au greffe',
            3 => 'Transmission au président',
            4 => 'Désignation du rapporteur',
            5 => 'Communication aux parties',
            6 => 'Dépôt des mémoires',
            7 => 'Réplique et duplique',
            8 => 'Clôture de l\'instruction',
            9 => 'Fixation de l\'audience',
            10 => 'Audience et plaidoiries',
            11 => 'Mise en délibéré',
        ];

        foreach ($etapes as $numero => $libelle) {
            EtapeRecours::create([
                'recours_id' => $this->id,
                'numero_etape' => $numero,
                'libelle' => $libelle,
                'statut' => $numero === 1 ? 'en_cours' : 'en_attente',
            ]);
        }
    }

    // Passer à l'étape suivante
    public function passerEtapeSuivante(): void
    {
        if ($this->etape_actuelle < 11) {
            // Compléter l'étape actuelle
            $etapeActuelle = $this->etapes()->where('numero_etape', $this->etape_actuelle)->first();
            if ($etapeActuelle) {
                $etapeActuelle->update([
                    'statut' => 'completee',
                    'date_completion' => now(),
                    'completee_par' => auth()->id(),
                ]);
            }

            // Activer l'étape suivante
            $this->etape_actuelle++;
            $this->save();

            $etapeSuivante = $this->etapes()->where('numero_etape', $this->etape_actuelle)->first();
            if ($etapeSuivante) {
                $etapeSuivante->update([
                    'statut' => 'en_cours',
                    'date_debut' => now(),
                ]);
            }
        } else {
            // Toutes les étapes complétées
            $this->statut_global = 'cloture';
            $this->save();
        }
    }
}
