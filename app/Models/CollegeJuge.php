<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollegeJuge extends Model
{
    use HasFactory;

    protected $fillable = [
        'tribunal_id',
        'designation',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function juges()
    {
        return $this->belongsToMany(Juge::class, 'college_juge_membres')
            ->withPivot('qualite')
            ->withTimestamps();
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }
}
