<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NatureSequestre extends Model
{
    protected $fillable = ['code', 'libelle', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function sequestres(): HasMany
    {
        return $this->hasMany(Sequestre::class);
    }
}
