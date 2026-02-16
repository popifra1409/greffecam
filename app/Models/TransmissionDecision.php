<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransmissionDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'decision_id',
        'expediteur_id',
        'destinataire_id',
        'motif',
        'statut',
        'observations_expediteur',
        'observations_destinataire',
        'date_transmission',
        'date_traitement',
    ];

    protected $casts = [
        'date_transmission' => 'datetime',
        'date_traitement' => 'datetime',
    ];

    // Relations
    public function decision()
    {
        return $this->belongsTo(Decision::class);
    }

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }
}
