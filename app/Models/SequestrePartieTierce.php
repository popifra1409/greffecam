<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequestrePartieTierce extends Model
{
    protected $table = 'sequestre_parties_tierces';

    protected $fillable = [
        'sequestre_id',
        'type_partie_tierce',
        'nom_complet',
        'telephone',
        'adresse',
        'reference',
        'observations',
    ];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type_partie_tierce) {
            'huissier' => 'Huissier',
            'avocat' => 'Avocat',
            'service_public' => 'Service public',
            'autre' => 'Autre',
            default => $this->type_partie_tierce,
        };
    }
}
