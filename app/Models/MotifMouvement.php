<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotifMouvement extends Model
{
    protected $table = 'motifs_mouvements';
    protected $fillable = ['code', 'libelle', 'type_mouvement', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementSequestre::class);
    }

    /**
     * Motifs disponibles pour un type de mouvement donné
     */
    public static function pourType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($type) {
                $q->where('type_mouvement', $type)
                    ->orWhere('type_mouvement', 'les_deux');
            })
            ->orderBy('libelle')
            ->get();
    }
}
