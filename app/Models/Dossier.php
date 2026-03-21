<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Dossier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'tribunal_id',
        'section_id',
        'matiere_id',
        'annee_judiciaire_id',
        'numero_dossier',
        'numero_dossier_personnalise',
        //demandeur
        'demandeur_est_personne_morale',
        'demandeur_nom',
        'demandeur_prenom',
        'demandeur_date_naissance',
        'demandeur_lieu_naissance',
        'demandeur_profession',
        'demandeur_nationalite',
        'demandeur_raison_sociale',
        'demandeur_representant_legal',
        'demandeur_adresse',
        'demandeur_telephone',
        'demandeur_email',
        'avocat_demandeur_nom',
        'avocat_demandeur_contact',
        // Défendeur
        'defendeur_est_personne_morale',
        'defendeur_nom',
        'defendeur_prenom',
        'defendeur_date_naissance',
        'defendeur_lieu_naissance',
        'defendeur_profession',
        'defendeur_nationalite',
        'defendeur_raison_sociale',
        'defendeur_representant_legal',
        'defendeur_adresse',
        'defendeur_telephone',
        'defendeur_email',
        'avocat_defendeur_nom',
        'avocat_defendeur_contact',
        'date_enrolement',
        'date_assignation',
        'date_premiere_audience',
        'date_cloture',
        'statut',
        'observations',
        'enrole_par',
    ];

    protected $casts = [
        'date_enrolement' => 'date',
        'date_assignation' => 'date',
        'date_premiere_audience' => 'date',
        'date_cloture' => 'date',
        'demandeur_date_naissance' => 'date',
        'defendeur_date_naissance' => 'date',
        'demandeur_est_personne_morale' => 'boolean',
        'defendeur_est_personne_morale' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations
    public function parties()
    {
        return $this->hasMany(DossierPartie::class);
    }

    public function demandeurs()
    {
        return $this->hasMany(DossierPartie::class)->where('type_partie', 'demandeur');
    }

    public function defendeurs()
    {
        return $this->hasMany(DossierPartie::class)->where('type_partie', 'defendeur');
    }

    public function partiesCiviles()
    {
        return $this->hasMany(DossierPartie::class)->where('type_partie', 'partie_civile');
    }

    public function prevenus()
    {
        return $this->hasMany(DossierPartie::class)->where('type_partie', 'prevenu');
    }

    public function temoins()
    {
        return $this->hasMany(DossierPartie::class)->where('type_partie', 'temoin');
    }


    // Relation avec les infractions
    public function infractions()
    {
        return $this->belongsToMany(Infraction::class, 'dossier_infractions');
    }

    // Accesseur pour le nom complet du défendeur
    public function getDefendeurNomCompletAttribute()
    {
        // Utiliser la nouvelle structure si elle existe
        $premierDefendeur = $this->defendeurs()->first();

        if ($premierDefendeur) {
            return $premierDefendeur->nom_complet;
        }

        // Sinon utiliser l'ancien système
        if ($this->defendeur_est_personne_morale) {
            return $this->defendeur_raison_sociale;
        }
        return trim($this->defendeur_nom . ' ' . $this->defendeur_prenom);
    }

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    public function anneeJudiciaire()
    {
        return $this->belongsTo(AnneeJudiciaire::class);
    }

    public function enrolePar()
    {
        return $this->belongsTo(User::class, 'enrole_par');
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    public function recours()
    {
        return $this->hasManyThrough(Recours::class, Decision::class);
    }

    // Accesseurs
    public function getDemandeurNomCompletAttribute()
    {
        // Utiliser la nouvelle structure si elle existe
        $premierDemandeur = $this->demandeurs()->first();

        if ($premierDemandeur) {
            return $premierDemandeur->nom_complet;
        }

        // Sinon utiliser l'ancien système
        if ($this->demandeur_est_personne_morale) {
            return $this->demandeur_raison_sociale;
        }
        return trim($this->demandeur_nom . ' ' . $this->demandeur_prenom);
    }

    public function getTypePartiesAttribute()
    {
        // Retourne les types de parties selon la section
        if ($this->section) {
            if ($this->section->type === 'repressive') {
                return ['Ministère Public', 'Partie Civile', 'Prévenu', 'Témoin'];
            }
            return ['Demandeur', 'Défendeur', 'Témoin'];
        }
        return ['Demandeur', 'Défendeur', 'Témoin'];
    }

    // Helpers
    public function peutEtreClos(): bool
    {
        // Un dossier peut être clos si toutes ses décisions sont archivées
        // ou si la grosse est délivrée et aucun recours en cours
        return in_array($this->statut, ['grosse_delivree', 'en_instance']);
    }

    public function getDemandeursListeAttribute()
    {
        return $this->demandeurs->pluck('nom_complet')->join(', ');
    }

    public function getDefendeursListeAttribute()
    {
        return $this->defendeurs->pluck('nom_complet')->join(', ');
    }

    // Générer un numéro de dossier unique
    public static function genererNumeroDossier($tribunalId, $sectionId, $matiereId, $annee)
    {
        $tribunal = Tribunal::find($tribunalId);
        $section = Section::find($sectionId);
        $matiere = Matiere::find($matiereId);

        // Format: CODE_TRIBUNAL/CODE_SECTION/CODE_MATIERE/ANNEE/NUMERO
        // Ex: TPI-YDE/CIV/TRAV/2025/00001

        $count = self::where('tribunal_id', $tribunalId)
            ->where('section_id', $sectionId)
            ->where('matiere_id', $matiereId)
            ->whereYear('date_enrolement', $annee)
            ->count() + 1;

        $tribunalCode = strtoupper(substr($tribunal->nom, 0, 3));
        $sectionCode = $section->code;
        $matiereCode = strtoupper(substr($matiere->designation, 0, 4));

        return sprintf(
            '%s/%s/%s/%s/%05d',
            $tribunalCode,
            $sectionCode,
            $matiereCode,
            $annee,
            $count
        );
    }
}
