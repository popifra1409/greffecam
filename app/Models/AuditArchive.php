<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditArchive extends Model
{
    protected $table = 'audit_archives';

    protected $fillable = [
        'nom_fichier',
        'periode_debut',
        'periode_fin',
        'nombre_entrees',
        'taille_octets',
        'chemin',
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'nombre_entrees' => 'integer',
        'taille_octets' => 'integer',
    ];

    public function getTailleLisibleAttribute(): string
    {
        $octets = (int) $this->taille_octets;

        if ($octets < 1024) {
            return $octets . ' o';
        }

        if ($octets < 1024 * 1024) {
            return number_format($octets / 1024, 1, ',', ' ') . ' Ko';
        }

        if ($octets < 1024 * 1024 * 1024) {
            return number_format($octets / (1024 * 1024), 1, ',', ' ') . ' Mo';
        }

        return number_format($octets / (1024 * 1024 * 1024), 2, ',', ' ') . ' Go';
    }
}
