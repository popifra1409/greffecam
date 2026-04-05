<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    protected $table = 'type_documents';

    protected $fillable = [
        'code',
        'libelle',
        'icone',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getLibelleAvecIconeAttribute(): string
    {
        return ($this->icone ? $this->icone . ' ' : '') . $this->libelle;
    }
}