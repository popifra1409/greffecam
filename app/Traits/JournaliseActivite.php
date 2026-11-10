<?php

namespace App\Traits;

use App\Support\JournalAudit;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Journalise automatiquement les créations, modifications et suppressions
 * d'un modèle dans le journal d'audit applicatif.
 */
trait JournaliseActivite
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return JournalAudit::options($this->getTable());
    }
}
