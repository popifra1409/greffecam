<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $fillable = ['code', 'libelle', 'type_grade', 'ordre', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function juges(): HasMany
    {
        return $this->hasMany(Juge::class);
    }

    public function greffiers(): HasMany
    {
        return $this->hasMany(Greffier::class);
    }

    public function scopePourJuges($query)
    {
        return $query->whereIn('type_grade', ['juge', 'les_deux'])->where('is_active', true);
    }

    public function scopePourGreffiers($query)
    {
        return $query->whereIn('type_grade', ['greffier', 'les_deux'])->where('is_active', true);
    }
}
