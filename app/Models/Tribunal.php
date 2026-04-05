<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tribunal extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nom',
        'sigle',
        'ville',
        'adresse',
        'telephone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nom', 'ville', 'is_active'])
            ->logOnlyDirty();
    }

    // Relations
    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    public function juges()
    {
        return $this->hasMany(Juge::class);
    }

    public function greffiers()
    {
        return $this->hasMany(Greffier::class);
    }
    
    public function getFormatNomenclature(): string
    {
        return strtoupper($this->sigle);
    }
}
