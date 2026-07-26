<?php

namespace App\Observers;

use App\Models\MouvementSequestre;
use App\Models\Sequestre;
use App\Services\SoldeSequestreService;

class MouvementSequestreObserver
{
    public function __construct(
        protected SoldeSequestreService $soldeService
    ) {}

    public function creating(MouvementSequestre $mouvement): void
    {
        $sequestre = Sequestre::find($mouvement->sequestre_id);

        if ($sequestre) {
            $this->soldeService->calculerMontants($mouvement, $sequestre);
        }
    }

    public function created(MouvementSequestre $mouvement): void
    {
        $sequestre = Sequestre::find($mouvement->sequestre_id);

        if ($sequestre) {
            $this->soldeService->recalculerSolde($sequestre);
        }
    }

    public function updating(MouvementSequestre $mouvement): void
    {
        if ($mouvement->isDirty(['montant_mouvement', 'type_mouvement'])) {
            $sequestre = Sequestre::find($mouvement->sequestre_id);

            if ($sequestre) {
                $this->soldeService->calculerMontants($mouvement, $sequestre);
            }
        }
    }

    public function updated(MouvementSequestre $mouvement): void
    {
        $sequestre = Sequestre::find($mouvement->sequestre_id);

        if ($sequestre) {
            $this->soldeService->recalculerSolde($sequestre);
        }
    }

    public function deleted(MouvementSequestre $mouvement): void
    {
        $sequestre = Sequestre::find($mouvement->sequestre_id);

        if ($sequestre) {
            $this->soldeService->recalculerSolde($sequestre);
        }
    }
}
