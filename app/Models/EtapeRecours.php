<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeRecours extends Model
{
    use HasFactory;

    protected $table = 'etape_recours';

    protected $fillable = [
        'recours_id',
        'numero_etape',
        'libelle',
        'statut',
        'date_debut',
        'date_completion',
        'date_limite',
        'description',
        'observations',
        'completee_par',
        'documents_generes',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_completion' => 'datetime',
        'date_limite' => 'date',
        'numero_etape' => 'integer',
        'documents_generes' => 'array',
    ];

    // Relations
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    public function completePar()
    {
        return $this->belongsTo(User::class, 'completee_par');
    }

    public function actes()
    {
        return $this->hasMany(ActeRecours::class, 'etape_recours_id');
    }

    // Helper
    public function peutEtreCompletee(): bool
    {
        return $this->statut === 'en_cours';
    }
}
