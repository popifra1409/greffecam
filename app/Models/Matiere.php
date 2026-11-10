<?php

namespace App\Models;

use App\Traits\JournaliseActivite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use JournaliseActivite;

    use HasFactory;

    protected $fillable = [
        'section_id',
        'designation',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id'); 
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }
}
