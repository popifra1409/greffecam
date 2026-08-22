<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NatureSequestre extends Model
{
    protected $fillable = [
        'code',
        'libelle',
        'description',
        'terme_ayants_droit',
        'terme_parties_adverses',
        'terme_partie_tierce',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function sequestres(): HasMany
    {
        return $this->hasMany(Sequestre::class);
    }
}
