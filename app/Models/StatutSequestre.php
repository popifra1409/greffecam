<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatutSequestre extends Model
{
    protected $fillable = ['code', 'libelle', 'couleur', 'bloque_mouvements', 'ordre', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'bloque_mouvements' => 'boolean',
    ];

    public function sequestres(): HasMany
    {
        return $this->hasMany(Sequestre::class);
    }
}
