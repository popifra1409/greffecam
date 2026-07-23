<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequestrePartieAdverse extends Model
{
    protected $table = 'sequestre_parties_adverses';

    protected $fillable = ['sequestre_id', 'nom_complet', 'numero_cni', 'telephone', 'adresse', 'observations'];

    public function sequestre(): BelongsTo
    {
        return $this->belongsTo(Sequestre::class);
    }
}
