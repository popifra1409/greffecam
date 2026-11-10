<?php

namespace App\Console\Commands;

use App\Models\AuditArchive;
use App\Support\JournalAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Activitylog\Models\Activity;

class ArchiverJournalAudit extends Command
{
    protected $signature = 'audit:archiver
                            {--mois= : Nombre de mois conservés dans le journal actif (défaut : configuration)}
                            {--dry-run : Simuler sans écrire de fichier ni supprimer d\'entrée}';

    protected $description = 'Archive les entrées anciennes du journal d\'audit dans des fichiers compressés, puis les retire du journal actif';

    public function handle(): int
    {
        $mois = max(0, (int) ($this->option('mois') ?? config('audit.retention_mois', 6)));
        $limite = now()->subMonthsNoOverflow($mois)->startOfDay();
        $dossier = (string) config('audit.archives_path');
        $tailleLot = max(100, (int) config('audit.chunk', 1000));

        $this->info(sprintf(
            'Conservation : %d mois — entrées antérieures au %s.',
            $mois,
            $limite->format('d/m/Y')
        ));

        $requete = fn() => Activity::query()->where('created_at', '<', $limite);

        $moisDisponibles = $requete()
            ->selectRaw("to_char(created_at, 'YYYY-MM') as mois, count(*) as nombre, min(created_at) as debut, max(created_at) as fin")
            ->groupByRaw("to_char(created_at, 'YYYY-MM')")
            ->orderBy('mois')
            ->get();

        if ($moisDisponibles->isEmpty()) {
            $this->info('Aucune entrée à archiver.');
            return self::SUCCESS;
        }

        foreach ($moisDisponibles as $ligne) {
            $this->line(sprintf('  %s : %s entrée(s)', $ligne->mois, number_format((int) $ligne->nombre, 0, ',', ' ')));
        }

        if ($this->option('dry-run')) {
            $this->warn('Mode simulation : aucun fichier écrit, aucune entrée supprimée.');
            return self::SUCCESS;
        }

        File::ensureDirectoryExists($dossier);
        $horodatage = now()->format('Ymd_His');
        $totalArchive = 0;

        foreach ($moisDisponibles as $ligne) {
            $nomFichier = "journal-audit-{$ligne->mois}-{$horodatage}.jsonl.gz";
            $chemin = rtrim($dossier, '/') . '/' . $nomFichier;
            $temporaire = $chemin . '.tmp';

            $flux = gzopen($temporaire, 'wb6');

            if ($flux === false) {
                $this->error("Impossible d'ouvrir le fichier temporaire {$temporaire}.");
                return self::FAILURE;
            }

            $nombreEcrit = 0;

            try {
                $requete()
                    ->whereRaw("to_char(created_at, 'YYYY-MM') = ?", [$ligne->mois])
                    ->chunkById($tailleLot, function ($entrees) use ($flux, &$nombreEcrit) {
                        foreach ($entrees as $entree) {
                            $ligneJson = json_encode([
                                'id' => $entree->id,
                                'log_name' => $entree->log_name,
                                'description' => $entree->description,
                                'subject_type' => $entree->subject_type,
                                'subject_id' => $entree->subject_id,
                                'causer_type' => $entree->causer_type,
                                'causer_id' => $entree->causer_id,
                                'properties' => $entree->properties?->toArray(),
                                'created_at' => $entree->created_at?->toIso8601String(),
                                'updated_at' => $entree->updated_at?->toIso8601String(),
                                'event' => $entree->event,
                                'batch_uuid' => $entree->batch_uuid,
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                            gzwrite($flux, $ligneJson . "\n");
                            $nombreEcrit++;
                        }
                    });
            } catch (\Throwable $exception) {
                gzclose($flux);
                File::delete($temporaire);

                $this->error("Échec de l'écriture de l'archive {$nomFichier} : {$exception->getMessage()}");
                return self::FAILURE;
            }

            gzclose($flux);
            rename($temporaire, $chemin);

            try {
                $supprimees = DB::transaction(function () use ($ligne, $chemin, $nomFichier, $nombreEcrit, $requete) {
                    AuditArchive::create([
                        'nom_fichier' => $nomFichier,
                        'periode_debut' => $ligne->debut,
                        'periode_fin' => $ligne->fin,
                        'nombre_entrees' => $nombreEcrit,
                        'taille_octets' => filesize($chemin),
                        'chemin' => $chemin,
                    ]);

                    return $requete()
                        ->whereRaw("to_char(created_at, 'YYYY-MM') = ?", [$ligne->mois])
                        ->delete();
                });
            } catch (\Throwable $exception) {
                $this->error("Archive {$nomFichier} écrite, mais suppression impossible : {$exception->getMessage()}");
                return self::FAILURE;
            }

            $totalArchive += $supprimees;

            $this->info(sprintf(
                '%s : %s entrée(s) archivée(s) → %s',
                $ligne->mois,
                number_format($supprimees, 0, ',', ' '),
                $nomFichier
            ));
        }

        JournalAudit::journaliser('systeme', 'archivage', [
            'entrees' => $totalArchive,
            'periodes' => $moisDisponibles->pluck('mois')->all(),
            'retention_mois' => $mois,
        ]);

        $this->info('Archivage terminé : ' . number_format($totalArchive, 0, ',', ' ') . ' entrée(s).');

        return self::SUCCESS;
    }
}
