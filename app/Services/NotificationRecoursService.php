<?php

namespace App\Services;

use App\Models\Recours;
use App\Models\User;
use App\Notifications\RecoursATraiterNotification;
use Illuminate\Support\Facades\Log;

class NotificationRecoursService
{
    /**
     * Envoyer le résumé quotidien
     */
    public function envoyerResumeQuotidien(): void
    {
        $statistiques = $this->getStatistiques();

        // Si aucun recours à traiter, ne rien envoyer (sauf si l'utilisateur veut le résumé vide)
        if ($statistiques['total'] === 0) {
            Log::info('Aucun recours à traiter - Pas de notification envoyée');
            return;
        }

        // Récupérer les utilisateurs qui veulent le résumé quotidien
        $utilisateurs = $this->getUtilisateursResumeQuotidien();

        foreach ($utilisateurs as $user) {
            $this->envoyerNotificationUtilisateur($user, $statistiques, 'resume');
        }

        Log::info('Résumé quotidien envoyé', [
            'utilisateurs' => $utilisateurs->count(),
            'statistiques' => $statistiques,
        ]);
    }

    /**
     * Envoyer les notifications urgentes
     */
    public function envoyerNotificationsUrgentes(): void
    {
        $statistiques = $this->getStatistiques();

        // Seulement si recours urgents (>30j)
        if ($statistiques['recours_urgents'] === 0) {
            return;
        }

        // Récupérer les utilisateurs qui veulent les alertes urgentes
        $utilisateurs = $this->getUtilisateursAlertesUrgentes();

        foreach ($utilisateurs as $user) {
            $this->envoyerNotificationUtilisateur($user, $statistiques, 'urgent');
        }

        Log::info('Notifications urgentes envoyées', [
            'utilisateurs' => $utilisateurs->count(),
            'recours_urgents' => $statistiques['recours_urgents'],
        ]);
    }

    /**
     * Envoyer une notification à un utilisateur
     */
    private function envoyerNotificationUtilisateur(User $user, array $statistiques, string $type): void
    {
        $preferences = $user->getOrCreateNotificationPreference();

        // Vérifier les préférences selon le type
        if ($type === 'resume' && !$preferences->resume_quotidien) {
            return;
        }

        if ($type === 'urgent' && !$preferences->recours_urgents) {
            return;
        }

        // Vérifier l'heure (sauf pour urgent)
        if ($type !== 'urgent' && !$preferences->estDansHeuresEnvoi()) {
            Log::info('Notification reportée - hors horaires', [
                'user_id' => $user->id,
                'type' => $type,
            ]);
            return;
        }

        try {
            $user->notify(new RecoursATraiterNotification($statistiques, $type));

            Log::info('Notification envoyée', [
                'user_id' => $user->id,
                'type' => $type,
                'canaux' => $preferences->getCanauxActifs(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculer les statistiques des recours
     */
    private function getStatistiques(): array
    {
        $recoursNonEnregistres = Recours::whereNull('date_enregistrement')
            ->where('created_at', '<=', now()->subDays(3))
            ->count();

        $recoursNonTransmis = Recours::whereNotNull('date_enregistrement')
            ->whereNull('date_transmission_cour_appel')
            ->where('date_enregistrement', '<=', now()->subDays(7))
            ->count();

        $recoursUrgents = Recours::whereNull('date_transmission_cour_appel')
            ->where('date_recours', '<=', now()->subDays(30))
            ->count();

        $total = Recours::whereNull('date_transmission_cour_appel')->count();

        return [
            'recours_non_enregistres' => $recoursNonEnregistres,
            'recours_non_transmis' => $recoursNonTransmis,
            'recours_urgents' => $recoursUrgents,
            'total' => $total,
        ];
    }

    /**
     * Utilisateurs qui veulent le résumé quotidien
     */
    private function getUtilisateursResumeQuotidien(): \Illuminate\Database\Eloquent\Collection
    {
        $heureActuelle = now()->format('H:i');

        return User::whereHas('notificationPreference', function ($query) use ($heureActuelle) {
            $query->where('resume_quotidien', true)
                ->whereTime('heure_resume', '<=', $heureActuelle);
        })->with('notificationPreference')->get();
    }

    /**
     * Utilisateurs qui veulent les alertes urgentes
     */
    private function getUtilisateursAlertesUrgentes(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('notificationPreference', function ($query) {
            $query->where('recours_urgents', true);
        })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'Super Administrateur',
                    'Administrateur',
                    'Greffier Chef',
                    'Greffier',
                ]);
            })
            ->with('notificationPreference')
            ->get();
    }
}