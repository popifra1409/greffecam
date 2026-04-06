<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationRecoursService;

class EnvoyerNotificationsRecours extends Command
{
    protected $signature = 'notifications:envoyer-recours {--type=resume}';

    protected $description = 'Envoyer les notifications de recours (resume ou urgent)';

    public function handle(NotificationRecoursService $service): int
    {
        $type = $this->option('type');

        $this->info("Envoi des notifications de type: {$type}");

        if ($type === 'urgent') {
            $service->envoyerNotificationsUrgentes();
            $this->info('✅ Notifications urgentes envoyées !');
        } else {
            $service->envoyerResumeQuotidien();
            $this->info('✅ Résumé quotidien envoyé !');
        }

        return 0;
    }
}