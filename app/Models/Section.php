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
        'libelle',
        'code',
        'description',
        'types_parties',
        'utilise_assesseur',
        'is_active',
    ];

    protected $casts = [
        'types_parties' => 'array',
        'utilise_assesseur' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['libelle', 'code', 'is_active'])
            ->logOnlyDirty();
    }

    // Relations
    public function matieres()
    {
        return $this->hasMany(Matiere::class);
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    public function infractions()
    {
        return $this->hasMany(Infraction::class);
    }

    // Accesseur pour obtenir les types de parties
    public function getTypesPartiesOptionsAttribute()
    {
        if (!$this->types_parties) {
            return [];
        }

        $options = [];
        foreach ($this->types_parties as $key => $label) {
            $options[$key] = $label;
        }
        return $options;
    }
}
