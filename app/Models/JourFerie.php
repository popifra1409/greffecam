<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class JourFerie extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'libelle',
        'date',
        'is_recurrent',
        'annee',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurrent' => 'boolean',
        'annee' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['libelle', 'date', 'annee'])
            ->logOnlyDirty();
    }

    // Scope pour récupérer les jours fériés d'une année
    public function scopeForYear($query, $year)
    {
        return $query->where('annee', $year);
    }
}
