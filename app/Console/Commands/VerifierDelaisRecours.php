<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recours;
use App\Models\AlerteRecours;
use App\Models\User;
use Carbon\Carbon;

class VerifierDelaisRecours extends Command
{
    protected $signature = 'recours:verifier-delais';

    protected $description = 'Vérifier les délais des recours et générer les alertes';

    public function handle()
    {
        $this->info('Vérification des délais des recours en cours...');

        // Récupérer tous les recours en cours
        $recours = Recours::where('statut_global', 'en_cours')
            ->where('statut_recevabilite', '!=', 'irrecevable')
            ->get();

        $alertesCreees = 0;

        foreach ($recours as $recour) {
            $niveauAlerte = $recour->niveau_alerte;

            if (!$niveauAlerte) {
                continue; // Pas d'alerte nécessaire
            }

            // Vérifier si une alerte de ce niveau existe déjà pour ce recours
            $alerteExiste = AlerteRecours::where('recours_id', $recour->id)
                ->where('niveau', $niveauAlerte)
                ->where('date_declenchement', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($alerteExiste) {
                continue; // Alerte déjà créée dans les dernières 24h
            }

            // Déterminer les destinataires
            $destinataires = [];

            // Ajouter le greffier responsable
            if ($recour->greffier_responsable_id) {
                $destinataires[] = $recour->greffier_responsable_id;
            }

            // Ajouter les greffiers en chef et administrateurs
            $admins = User::role(['Administrateur', 'Greffier en Chef'])->pluck('id')->toArray();
            $destinataires = array_merge($destinataires, $admins);

            $destinataires = array_unique($destinataires);

            // Générer le message selon le niveau
            $message = $this->genererMessage($recour, $niveauAlerte);

            // Créer l'alerte
            AlerteRecours::create([
                'recours_id' => $recour->id,
                'niveau' => $niveauAlerte,
                'titre' => $this->genererTitre($niveauAlerte),
                'message' => $message,
                'date_declenchement' => now(),
                'destinataires_ids' => $destinataires,
                'est_lue' => false,
                'est_envoyee' => false,
            ]);

            $alertesCreees++;

            $this->info("Alerte {$niveauAlerte} créée pour le recours {$recour->numero_recours}");
        }

        $this->info("Vérification terminée. {$alertesCreees} alertes créées.");

        return Command::SUCCESS;
    }

    private function genererTitre(string $niveau): string
    {
        return match ($niveau) {
            'rouge' => '⚠️ URGENT : Délai critique (H-48)',
            'orange' => '⚠️ ATTENTION : Délai court (J-7)',
            'jaune' => 'ℹ️ INFO : Délai à surveiller (J-15)',
            default => 'Notification',
        };
    }

    private function genererMessage(Recours $recour, string $niveau): string
    {
        $joursRestants = $recour->jours_restants;
        $dateLimite = $recour->date_limite_recours->format('d/m/Y');

        return match ($niveau) {
            'rouge' => "Le recours {$recour->numero_recours} arrive à échéance dans {$joursRestants} jour(s) ouvrables (date limite : {$dateLimite}). Action immédiate requise !",
            'orange' => "Le recours {$recour->numero_recours} arrive à échéance dans {$joursRestants} jours ouvrables (date limite : {$dateLimite}). Veuillez prendre les mesures nécessaires.",
            'jaune' => "Le recours {$recour->numero_recours} arrive à échéance dans {$joursRestants} jours ouvrables (date limite : {$dateLimite}). À surveiller.",
            default => "Notification pour le recours {$recour->numero_recours}",
        };
    }
}
