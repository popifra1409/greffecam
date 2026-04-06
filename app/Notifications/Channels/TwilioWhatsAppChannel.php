<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppChannel
{
    protected TwilioClient $client;

    public function __construct()
    {
        $this->client = new TwilioClient(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
    }

    /**
     * Envoyer la notification
     */
    public function send($notifiable, Notification $notification): void
    {
        $to = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (!$to) {
            Log::warning('Pas de numéro WhatsApp pour l\'utilisateur', [
                'user_id' => $notifiable->id,
            ]);
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        try {
            $this->client->messages->create(
                $to, // déjà au format whatsapp:+237...
                [
                    'from' => config('services.twilio.whatsapp_from'),
                    'body' => $message['content'] ?? $message,
                ]
            );

            Log::info('WhatsApp envoyé avec succès', [
                'to' => $to,
                'user_id' => $notifiable->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi WhatsApp Twilio', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);
        }
    }
}