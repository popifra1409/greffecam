<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juge extends Model
{
    use HasFactory;

    protected $fillable = [
        'tribunal_id',
        'matricule',
        'nom',
        'prenom',
        'titre',
        'grade',
        'email',
        'telephone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function collegeJuges()
    {
        return $this->belongsToMany(CollegeJuge::class, 'college_juge_membres')
            ->withPivot('qualite')
            ->withTimestamps();
    }

    public function getNomCompletAttribute()
    {
        return trim(($this->titre ? $this->titre . ' ' : '') . $this->nom . ' ' . $this->prenom);
    }

    //Relations
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
