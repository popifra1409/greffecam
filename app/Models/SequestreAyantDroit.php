<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequestreAyantDroit extends Model
{
    protected $table = 'sequestre_ayants_droits';

    protected $fillable = [
        'sequestre_id',
        'nom_complet',
        'numero_cni',
        'telephone',
        'adresse',
        'observations',
        'role_succession',
        'pourcentage_manuel',
    ];

    protected $casts = [
        'pourcentage_manuel' => 'decimal:2',
    ];

    public function getRoleSuccessionLabelAttribute(): string
    {
        return match ($this->role_succession) {
            'conjoint' => 'Conjoint',
            'enfant' => 'Enfant',
            'autre' => 'Autre',
            default => 'Non défini',
        };
    }

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }
}
