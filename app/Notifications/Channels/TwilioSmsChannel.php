<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

class TwilioSmsChannel
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
        $to = $notifiable->routeNotificationFor('twilio', $notification);

        if (!$to) {
            Log::warning('Pas de numéro de téléphone pour l\'utilisateur', [
                'user_id' => $notifiable->id,
            ]);
            return;
        }

        $message = $notification->toTwilio($notifiable);

        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => config('services.twilio.phone_number'),
                    'body' => $message['content'] ?? $message,
                ]
            );

            Log::info('SMS envoyé avec succès', [
                'to' => $to,
                'user_id' => $notifiable->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi SMS Twilio', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);
        }
    }
}