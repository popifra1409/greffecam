<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActeRecours extends Model
{
    use HasFactory;

    protected $table = 'acte_recours';

    protected $fillable = [
        'recours_id',
        'etape_recours_id',
        'type_acte',
        'numero_acte',
        'libelle',
        'contenu',
        'fichier_path',
        'date_generation',
        'date_envoi',
        'date_reception',
        'destinataire',
        'adresse_destinataire',
        'genere_par',
    ];

    protected $casts = [
        'date_generation' => 'date',
        'date_envoi' => 'date',
        'date_reception' => 'date',
    ];

    // Relations
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    public function etapeRecours()
    {
        return $this->belongsTo(EtapeRecours::class);
    }

    public function generePar()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }
}
