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
        'section_id',
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
            ->logOnly(['numero_rg', 'statut', 'date_decision'])
            ->logOnlyDirty();
    }

    // Relations
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
        return $this->belongsTo(Section::class);
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
