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
        'dossier_id',
        'numero_repertoire',
        'numero_parquet',
        //ENREGISTREMENT
        'numero_volume',
        'numero_folio',
        'numero_case_bd',
        'numero_quittance',
        'montant_quittance',

        'nature_decision_id',
        'tribunal_id',
        'section_id',
        'matiere_id',
        'annee_judiciaire_id',
        //DATES
        'date_decision',
        'date_premiere_audience',
        'date_factum',
        'date_saisie',
        'date_modification',
        'date_signature',
        'date_enregistrement',

        //COMPOSITION
        'mode_composition',
        'juge_unique_id',
        'college_juge_id',
        'greffier_id',

        // Anciens champs compatibilité
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
        //FICHIERS
        'fichier_scan',
        'fichier_saisi',
        'fichier_saisi_modifie',
        'fichier_signe',
        'fichier_enregistre',
        //CERTIFICAT ET GROSSE
        'certificat_non_appel_reference',
        'certificat_non_appel_date',
        'certificat_non_appel_fichier',
        'grosse_reference',
        'grosse_date',
        'grosse_fichier',
        //OPPOSITION
        'a_opposition',
        'lettre_opposition_reference',
        'lettre_opposition_date',
        'lettre_opposition_fichier',

        'greffier_responsable_id',
        'detenteur_actuel_id',
        'is_archived',
        'date_archivage',
        'motif_transmission',
    ];

    protected $casts = [
        'date_decision' => 'date',
        'date_premiere_audience' => 'date',
        'date_factum' => 'date',
        'date_saisie' => 'date',
        'date_modification' => 'date',
        'date_signature' => 'date',
        'date_enregistrement' => 'date',
        'date_archivage' => 'date',
        'certificat_non_appel_date' => 'date',
        'grosse_date' => 'date',
        'lettre_opposition_date' => 'date',
        'montant_amende' => 'decimal:2',
        'montant_depens' => 'decimal:2',
        'montant_quittance' => 'decimal:2',
        'is_archived' => 'boolean',
        'a_opposition' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ✅ NOUVELLES RELATIONS
    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    public function jugeUnique()
    {
        return $this->belongsTo(Juge::class, 'juge_unique_id');
    }

    public function collegeJuge()
    {
        return $this->belongsTo(CollegeJuge::class);
    }

    public function greffierDecision()
    {
        return $this->belongsTo(Greffier::class, 'greffier_id');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    // Relations existantes
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

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
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

    public function anneeJudiciaire()
    {
        return $this->belongsTo(AnneeJudiciaire::class);
    }

    // ✅ ACCESSEURS POUR COMPOSITION
    public function getCompositionAttribute()
    {
        if ($this->mode_composition === 'juge_unique') {
            return $this->jugeUnique?->nom_complet ?? 'Juge unique non défini';
        }

        return $this->collegeJuge?->designation ?? 'Collège non défini';
    }

    // Helpers
    public function estModifiable(): bool
    {
        return in_array($this->statut, ['brouillon', 'validee']);
    }

    public function peutEtreValidee(): bool
    {
        return $this->statut === 'brouillon';
    }

    public function peutEtreSaisie(): bool
    {
        return $this->statut === 'validee';
    }

    public function peutEtreSignee(): bool
    {
        return $this->statut === 'saisie';
    }

    public function peutEtreEnregistree(): bool
    {
        return $this->statut === 'signee';
    }

    public function peutEtreArchivee(): bool
    {
        return $this->statut === 'enregistree';
    }

    public function peutEtreTransmise(): bool
    {
        return $this->statut === 'brouillon';
    }

    public function estVisiblePar(User $user): bool
    {
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
}
