<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TwilioSmsChannel;
use App\Notifications\Channels\TwilioWhatsAppChannel;

class RecoursATraiterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $statistiques,
        public string $type = 'resume' // 'resume' ou 'urgent'
    ) {
    }

    /**
     * Canaux de notification
     */
    public function via(object $notifiable): array
    {
        $preferences = $notifiable->getOrCreateNotificationPreference();

        // Déterminer l'urgence
        $niveau = $this->type === 'urgent' ? 'urgent' : 'normal';

        $canaux = $preferences->getCanauxPourUrgence($niveau);

        // Mapper les canaux vers les classes
        return array_map(function ($canal) {
            return match ($canal) {
                'mail' => 'mail',
                'database' => 'database',
                'sms' => TwilioSmsChannel::class,
                'whatsapp' => TwilioWhatsAppChannel::class,
                default => $canal,
            };
        }, $canaux);
    }

    /**
     * Notification Email
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = new MailMessage();

        if ($this->type === 'urgent') {
            $message->subject('🔴 URGENT : Recours à traiter immédiatement')
                ->greeting("Bonjour {$notifiable->name},")
                ->line("**Attention : Des recours nécessitent une action urgente !**");
        } else {
            $message->subject('📋 Résumé quotidien - Recours à traiter')
                ->greeting("Bonjour {$notifiable->name},")
                ->line("Voici le résumé des recours en attente de traitement :");
        }

        // Statistiques
        if ($this->statistiques['recours_non_enregistres'] > 0) {
            $message->line("📋 **{$this->statistiques['recours_non_enregistres']} recours** à enregistrer (>3 jours)");
        }

        if ($this->statistiques['recours_non_transmis'] > 0) {
            $message->line("📤 **{$this->statistiques['recours_non_transmis']} recours** à transmettre à la CA (>7 jours)");
        }

        if ($this->statistiques['recours_urgents'] > 0) {
            $message->line("🔴 **{$this->statistiques['recours_urgents']} recours URGENTS** (>30 jours)");
        }

        if ($this->statistiques['total'] === 0) {
            $message->line("✅ Tous les recours sont à jour !");
        }

        $message->action('Voir les recours', url('/decision-recours/recours'))
            ->line("Merci de traiter ces recours dans les meilleurs délais.")
            ->salutation("Cordialement,\nSystème de Gestion du Greffe");

        return $message;
    }

    /**
     * Notification SMS (Twilio)
     */
    public function toTwilio(object $notifiable): array
    {
        $total = $this->statistiques['total'];
        $urgents = $this->statistiques['recours_urgents'];

        if ($this->type === 'urgent') {
            $message = "🔴 URGENT : {$urgents} recours urgent(s) à traiter immédiatement ! Consultez le système.";
        } else {
            $message = "📋 Greffe : {$total} recours en attente de traitement. ";
            if ($urgents > 0) {
                $message .= "{$urgents} URGENT(S). ";
            }
            $message .= "Consultez le système.";
        }

        return [
            'content' => $message,
        ];
    }

    /**
     * Notification WhatsApp (Twilio)
     */
    public function toWhatsApp(object $notifiable): array
    {
        $message = "";

        if ($this->type === 'urgent') {
            $message = "🔴 *URGENT - RECOURS À TRAITER*\n\n";
        } else {
            $message = "📋 *RÉSUMÉ QUOTIDIEN - GREFFE*\n\n";
        }

        $message .= "*Recours en attente :*\n";

        if ($this->statistiques['recours_non_enregistres'] > 0) {
            $message .= "📋 {$this->statistiques['recours_non_enregistres']} à enregistrer\n";
        }

        if ($this->statistiques['recours_non_transmis'] > 0) {
            $message .= "📤 {$this->statistiques['recours_non_transmis']} à transmettre CA\n";
        }

        if ($this->statistiques['recours_urgents'] > 0) {
            $message .= "🔴 {$this->statistiques['recours_urgents']} URGENTS (>30j)\n";
        }

        if ($this->statistiques['total'] === 0) {
            $message .= "✅ Tous les recours sont à jour !\n";
        }

        $message .= "\n🔗 Connectez-vous au système pour traiter les recours.";

        return [
            'content' => $message,
        ];
    }

    /**
     * Notification Database (Push)
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'statistiques' => $this->statistiques,
            'message' => $this->genererMessageCourt(),
        ];
    }

    private function genererMessageCourt(): string
    {
        $total = $this->statistiques['total'];
        $urgents = $this->statistiques['recours_urgents'];

        if ($total === 0) {
            return "✅ Tous les recours sont à jour";
        }

        if ($urgents > 0) {
            return "🔴 {$urgents} recours urgent(s) sur {$total} à traiter";
        }

        return "📋 {$total} recours en attente de traitement";
    }
}