<?php

namespace App\Models;

use App\Traits\JournaliseActivite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BanqueReleve extends Model
{
    use JournaliseActivite;

    protected $table = 'banque_releves';

    protected $fillable = [
        'sequestre_id',
        'date_releve',
        'solde_banque',
        'observations',
        'saisi_par',
    ];

    protected $casts = [
        'date_releve' => 'date',
        'solde_banque' => 'decimal:2',
    ];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }

    public function saisiPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }
}
