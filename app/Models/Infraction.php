<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Infraction extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'libelle',
        'code',
        'description',
        'categorie',
        'type_section_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['libelle', 'code', 'categorie', 'is_active'])
            ->logOnlyDirty();
    }

    // Relation avec les décisions (on la créera plus tard)
    public function decisions()
    {
        return $this->belongsToMany(Decision::class, 'decision_infractions');
    }
    // Ajouter la relation
    public function typeSection()
    {
        return $this->belongsTo(TypeSection::class);
    }
}
