<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Section extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tribunal_id',
        'type_section_id',
        'nom',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nom', 'code', 'is_active'])
            ->logOnlyDirty();
    }

    // Relations
    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function typeSection()
    {
        return $this->belongsTo(TypeSection::class);
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    public function greffiers()
    {
        return $this->belongsToMany(User::class, 'greffier_section')
            ->withPivot(['date_affectation', 'date_fin_affectation', 'is_active'])
            ->withTimestamps();
    }

    public function greffiersActifs()
    {
        return $this->greffiers()->wherePivot('is_active', true);
    }

    // Helper pour obtenir les types de parties selon la section
    public function getTypesPartiesAttribute()
    {
        return $this->typeSection?->types_parties_options ?? [];
    }

    // Helper pour savoir si la section utilise des assesseurs
    public function getUtiliseAssesseurAttribute()
    {
        return $this->typeSection?->utilise_assesseur ?? false;
    }
}
