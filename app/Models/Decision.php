<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Decision extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'numero_rg',
        'numero_repertoire',
        'numero_parquet',
        'nature_decision_id',
        'nature_rendu',
        'tribunal_id',
        'type_section_id',
        'annee_judiciaire_id',
        'date_decision',
        'date_signature',
        'date_factum',
        'date_enregistrement',
        'date_saisie',
        'president',
        'juge_1',
        'juge_2',
        'assesseur',
        'greffier',
        'ministere_public',
        'resume',
        'dispositif',
        'montant_amende',
        'montant_depens',
        'duree_peine',
        'statut',
        'fichier_scan',
        'greffier_responsable_id',
        'is_archived',
        'date_archivage',
        'motif_transmission',
    ];

    protected $casts = [
        'date_decision' => 'date',
        'date_signature' => 'date',
        'date_factum' => 'date',
        'date_enregistrement' => 'date',
        'date_saisie' => 'datetime',
        'date_archivage' => 'date',
        'montant_amende' => 'decimal:2',
        'montant_depens' => 'decimal:2',
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
    public function validateurUser()
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

    public function natureDecision()
    {
        return $this->belongsTo(NatureDecision::class);
    }

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function typeSection()
    {
        return $this->belongsTo(TypeSection::class);
    }

    public function detenteurActuel()
    {
        return $this->belongsTo(User::class, 'detenteur_actuel_id');
    }

    public function transmissions()
    {
        return $this->hasMany(TransmissionDecision::class)->orderBy('date_transmission', 'desc');
    }


    public function parties()
    {
        return $this->hasMany(Partie::class);
    }

    public function infractions()
    {
        return $this->belongsToMany(Infraction::class, 'decision_infractions');
    }

    public function greffierResponsable()
    {
        return $this->belongsTo(User::class, 'greffier_responsable_id');
    }

    public function estModifiable(): bool
    {
        return $this->statut === 'brouillon';
    }

    public function peutEtreValidee(): bool
    {
        return $this->statut === 'brouillon';
    }

    public function peutEtreAnnulee(): bool
    {
        return in_array($this->statut, ['validee', 'transmise_chef', 'signee']);
    }
    // Helper pour vérifier si peut être transmise
    public function peutEtreTransmise(): bool
    {
        return $this->statut === 'brouillon';
    }

    // Helper pour vérifier si peut être signée
    public function peutEtreSignee(): bool
    {
        return $this->statut === 'transmise_chef';
    }

    // Helper pour vérifier si peut être enregistrée
    public function peutEtreEnregistree(): bool
    {
        return $this->statut === 'signee';
    }


    // Helper pour vérifier la visibilité
    public function estVisiblePar(User $user): bool
    {
        // L'utilisateur peut voir si :
        // 1. Il est le détenteur actuel
        // 2. Il est l'auteur/greffier responsable
        // 3. Il est admin ou greffier en chef

        if ($this->detenteur_actuel_id === $user->id) {
            return true;
        }

        if ($this->greffier_responsable_id === $user->id) {
            return true;
        }

        if ($user->hasAnyRole(['Administrateur', 'Greffier en Chef'])) {
            return true;
        }

        return false;
    }
    // Scopes
    public function scopeNonArchivees($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchivees($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeParStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeParAnnee($query, $annee)
    {
        return $query->whereYear('date_decision', $annee);
    }

    public function anneeJudiciaire()
    {
        return $this->belongsTo(AnneeJudiciaire::class);
    }
}
