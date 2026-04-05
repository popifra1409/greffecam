<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recours extends Model
{
    use HasFactory;

    protected $table = 'recours';

    protected $fillable = [
        'decision_id',
        'numero_recours',
        'type_recours',
        'type_recours_id', 
        'date_recours',
        'reference_lettre',
        'fichier_lettre',
        'date_enregistrement',
        'date_transmission_cour_appel',
        'documents_mise_en_etat',
    ];

    protected $casts = [
        'date_recours' => 'date',
        'date_enregistrement' => 'date',
        'date_transmission_cour_appel' => 'date',
        'documents_mise_en_etat' => 'array',
    ];

    // Relations
    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }

    public function typeRecours(): BelongsTo
    {
        return $this->belongsTo(TypeRecours::class);
    }

    // Méthodes helper
    public function getTypeLabelAttribute(): string
    {
        if ($this->typeRecours) {
            return ($this->typeRecours->icone ?? '⚖️') . ' ' . $this->typeRecours->libelle;
        }

        // Fallback sur type_recours string
        return match ($this->type_recours) {
            'appel' => '⚖️ Appel',
            'opposition' => '⚠️ Opposition',
            'tierce_opposition' => '👥 Tierce opposition',
            'retractation' => '🔄 Rétractation',
            'revision' => '🔍 Révision',
            'pourvoi_cassation' => '⚖️ Pourvoi en cassation',
            default => $this->type_recours ?? 'Non défini',
        };
    }

    public function getNombreDocumentsAttribute(): int
    {
        return count($this->documents_mise_en_etat ?? []);
    }

    // Boot - générer numéro automatique
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recours) {
            if (empty($recours->numero_recours)) {
                $year = now()->year;
                $count = self::whereYear('created_at', $year)->count() + 1;
                $recours->numero_recours = 'REC/' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}