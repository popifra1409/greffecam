<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlerteRecours extends Model
{
    use HasFactory;

    protected $table = 'alerte_recours';

    protected $fillable = [
        'recours_id',
        'niveau',
        'titre',
        'message',
        'date_declenchement',
        'date_lecture',
        'destinataires_ids',
        'est_lue',
        'est_envoyee',
    ];

    protected $casts = [
        'date_declenchement' => 'datetime',
        'date_lecture' => 'datetime',
        'destinataires_ids' => 'array',
        'est_lue' => 'boolean',
        'est_envoyee' => 'boolean',
    ];

    // Relations
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    // Scope
    public function scopeNonLues($query)
    {
        return $query->where('est_lue', false);
    }

    public function scopeParNiveau($query, string $niveau)
    {
        return $query->where('niveau', $niveau);
    }
}
