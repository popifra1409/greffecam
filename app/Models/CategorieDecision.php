<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function typesDecisions()
    {
        return $this->hasMany(TypeDecision::class);
    }
}
