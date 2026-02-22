<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'categorie_decision_id',
        'libelle',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categorieDecision()
    {
        return $this->belongsTo(CategorieDecision::class);
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }
}
