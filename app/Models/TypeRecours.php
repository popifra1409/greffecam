<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TypeRecours extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'type_recours';

    protected $fillable = [
        'libelle',
        'code',
        'description',
        'delai_jours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delai_jours' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['libelle', 'code', 'delai_jours', 'is_active'])
            ->logOnlyDirty();
    }

    // Relation avec les recours (on la créera plus tard)
    public function recours()
    {
        return $this->hasMany(Recours::class);
    }
}
