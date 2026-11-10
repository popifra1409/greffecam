<?php

namespace App\Services;

use App\Models\MouvementSequestre;
use App\Models\Sequestre;
use App\Support\JournalAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * États PDF ciblés des mouvements d'un séquestre.
 *
 * Les cinq vues demandées sont dérivées des filtres :
 *  - un locataire          : partie_adverse_id ;
 *  - tous les locataires   : versements_locataires ;
 *  - un ayant droit        : ayant_droit_id ;
 *  - tous les ayants droit : retraits_ayants_droit ;
 *  - autres retraits       : autres_retraits (huissier, réfection, divers…).
 */
class MouvementsEtatPdfService
{
    public function requete(Sequestre $sequestre, array $filtres): Builder
    {
        return MouvementSequestre::query()
            ->where('sequestre_id', $sequestre->id)
            ->with(['motifMouvement', 'partieAdverse', 'ayantDroit', 'partieTierce'])
            ->when($filtres['date_debut'] ?? null, fn(Builder $q, $date) => $q->whereDate('date_mouvement', '>=', $date))
            ->when($filtres['date_fin'] ?? null, fn(Builder $q, $date) => $q->whereDate('date_mouvement', '<=', $date))
            ->when($filtres['type_mouvement'] ?? null, fn(Builder $q, $type) => $q->where('type_mouvement', $type))
            ->when($filtres['versements_locataires'] ?? false, fn(Builder $q) => $q
                ->where('type_mouvement', 'versement')
                ->whereNotNull('sequestre_partie_adverse_id'))
            ->when($filtres['retraits_ayants_droit'] ?? false, fn(Builder $q) => $q
                ->where('type_mouvement', 'retrait')
                ->whereNotNull('sequestre_ayant_droit_id'))
            ->when($filtres['autres_retraits'] ?? false, fn(Builder $q) => $q
                ->where('type_mouvement', 'retrait')
                ->whereNull('sequestre_ayant_droit_id'))
            ->when($filtres['partie_adverse_id'] ?? null, fn(Builder $q, $id) => $q->where('sequestre_partie_adverse_id', $id))
            ->when($filtres['ayant_droit_id'] ?? null, fn(Builder $q, $id) => $q->where('sequestre_ayant_droit_id', $id))
            ->orderBy('date_mouvement')
            ->orderBy('id');
    }

    public function generer(Sequestre $sequestre, array $filtres): \Barryvdh\DomPDF\PDF
    {
        ini_set('memory_limit', '512M');

        $sequestre->load([
            'dossier.tribunal',
            'decision.typeDecision',
            'decision.natureDecision',
            'representant',
            'natureSequestre',
            'statutSequestre',
            'ayantsDroit',
            'partiesAdverses',
        ]);

        $mouvements = $this->requete($sequestre, $filtres)->get();

        $data = [
            'sequestre' => $sequestre,
            'mouvements' => $mouvements,
            'groupes' => $this->grouper($mouvements),
            'titre' => $this->titre($sequestre, $filtres),
            'entite' => $this->libelleEntite($sequestre, $filtres),
            'recap' => $this->recap($sequestre, $filtres, $mouvements),
            'dateDebut' => $filtres['date_debut'] ?? null,
            'dateFin' => $filtres['date_fin'] ?? null,
            'totalEntrees' => (float) $mouvements->where('type_mouvement', 'versement')->sum('montant_mouvement'),
            'totalSorties' => (float) $mouvements->where('type_mouvement', 'retrait')->sum('montant_mouvement'),
            'totalPrecompte' => (float) $mouvements->sum('montant_precompte'),
            'soldeFinal' => $mouvements->last()?->solde_apres,
            'dateImpression' => now(),
        ];

        $pdf = Pdf::loadView('sequestres.mouvements-etat-pdf', $data);

        $pdf->setPaper('a4', 'portrait');

        JournalAudit::journaliser('rapports', 'pdf_genere', [
            'rapport' => 'État des mouvements',
            'numero' => $sequestre->numero_dossier_sequestre,
            'filtres' => array_filter($filtres, fn($valeur) => filled($valeur)),
            'mouvements' => $mouvements->count(),
        ], $sequestre);

        return $pdf;
    }

    /**
     * Regroupe les mouvements par type de bénéficiaire, dans un ordre stable :
     * locataires, ayants droit, parties tierces, puis bénéficiaires non renseignés.
     *
     * @return Collection<string, array{type: string, lignes: Collection, entrees: float, sorties: float, precompte: float}>
     */
    protected function grouper(Collection $mouvements): Collection
    {
        return $mouvements
            ->groupBy(fn(MouvementSequestre $mouvement) => $mouvement->type_beneficiaire ?? 'Non renseigné')
            ->sortBy(function (Collection $lignes, string $type): string {
                $rang = match (true) {
                    $type === 'Locataire' => 1,
                    str_starts_with($type, 'Ayant droit') => 2,
                    str_starts_with($type, 'Partie tierce') => 3,
                    default => 9,
                };

                return $rang . '|' . $type;
            })
            ->map(fn(Collection $lignes, string $type) => [
                'type' => $type,
                'lignes' => $lignes,
                'entrees' => (float) $lignes->where('type_mouvement', 'versement')->sum('montant_mouvement'),
                'sorties' => (float) $lignes->where('type_mouvement', 'retrait')->sum('montant_mouvement'),
                'precompte' => (float) $lignes->sum('montant_precompte'),
            ]);
    }

    protected function titre(Sequestre $sequestre, array $filtres): string
    {
        if ($filtres['ayant_droit_id'] ?? null) {
            return 'RETRAITS DE L\'AYANT DROIT';
        }

        if ($filtres['retraits_ayants_droit'] ?? false) {
            return 'RETRAITS DE TOUS LES AYANTS DROIT';
        }

        if ($filtres['autres_retraits'] ?? false) {
            return 'AUTRES RETRAITS (HUISSIER, RÉFECTION, DIVERS)';
        }

        if ($filtres['partie_adverse_id'] ?? null) {
            return 'VERSEMENTS DU LOCATAIRE';
        }

        if ($filtres['versements_locataires'] ?? false) {
            return 'VERSEMENTS DE TOUS LES LOCATAIRES';
        }

        return match ($filtres['type_mouvement'] ?? null) {
            'versement' => 'VERSEMENTS',
            'retrait' => 'RETRAITS',
            default => 'JOURNAL DES MOUVEMENTS',
        };
    }

    protected function libelleEntite(Sequestre $sequestre, array $filtres): ?string
    {
        if ($filtres['ayant_droit_id'] ?? null) {
            return $sequestre->ayantsDroit->firstWhere('id', $filtres['ayant_droit_id'])?->nom_complet;
        }

        if ($filtres['partie_adverse_id'] ?? null) {
            return $sequestre->partiesAdverses->firstWhere('id', $filtres['partie_adverse_id'])?->nom_complet;
        }

        return null;
    }

    /**
     * Bandeau de synthèse : uniquement pour les états nominatifs (un locataire,
     * un ayant droit), pour éviter d'alourdir les états consolidés.
     */
    protected function recap(Sequestre $sequestre, array $filtres, Collection $mouvements): array
    {
        if ($filtres['ayant_droit_id'] ?? null) {
            $ayantDroit = $sequestre->ayantsDroit->firstWhere('id', $filtres['ayant_droit_id']);

            if (! $ayantDroit) {
                return [];
            }

            $historique = $sequestre->mouvements()
                ->where('type_mouvement', 'retrait')
                ->where('sequestre_ayant_droit_id', $ayantDroit->id);

            return [
                ['label' => 'Ayant droit', 'valeur' => $ayantDroit->nom_complet],
                ['label' => 'Rôle dans la succession', 'valeur' => $ayantDroit->role_succession_label],
                ['label' => 'Part réservée', 'valeur' => $ayantDroit->pourcentage_manuel !== null
                    ? number_format((float) $ayantDroit->pourcentage_manuel, 2, ',', ' ') . ' %'
                    : 'Selon la répartition du séquestre'],
                ['label' => 'Total perçu (historique)', 'valeur' => number_format((float) (clone $historique)->sum('montant_mouvement'), 0, ',', ' ') . ' FCFA'],
                ['label' => 'Retraits par procuration', 'valeur' => (clone $historique)->where('est_procuration', true)->count() . ' sur ' . (clone $historique)->count()],
            ];
        }

        if ($filtres['partie_adverse_id'] ?? null) {
            $partieAdverse = $sequestre->partiesAdverses->firstWhere('id', $filtres['partie_adverse_id']);

            if (! $partieAdverse) {
                return [];
            }

            $situation = $partieAdverse->situation_actuelle;

            return [
                ['label' => 'Locataire', 'valeur' => $partieAdverse->nom_complet],
                ['label' => 'Échéance', 'valeur' => $partieAdverse->montant_echeance !== null
                    ? number_format((float) $partieAdverse->montant_echeance, 0, ',', ' ') . ' FCFA — ' . $partieAdverse->periodicite_label
                    : 'Non configurée'],
                ['label' => 'Statut de l\'échéancier', 'valeur' => $situation['statut_label']],
                ['label' => 'Reste à payer', 'valeur' => number_format((float) $situation['reste_a_payer'], 0, ',', ' ') . ' FCFA'],
                ['label' => 'Versé sur la période', 'valeur' => number_format((float) $mouvements->where('type_mouvement', 'versement')->sum('montant_mouvement'), 0, ',', ' ') . ' FCFA'],
            ];
        }

        return [];
    }
}
