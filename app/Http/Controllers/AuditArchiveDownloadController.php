<?php

namespace App\Http\Controllers;

use App\Models\AuditArchive;
use App\Support\JournalAudit;

class AuditArchiveDownloadController extends Controller
{
    public function __invoke(AuditArchive $archive)
    {
        abort_unless(JournalAudit::peutConsulter(auth()->user()), 403);

        if (! is_file($archive->chemin)) {
            abort(404, 'Le fichier d\'archive est introuvable sur le serveur.');
        }

        JournalAudit::journaliser('systeme', 'archive_telechargee', [
            'fichier' => $archive->nom_fichier,
            'entrees' => $archive->nombre_entrees,
        ], $archive);

        return response()->download($archive->chemin, $archive->nom_fichier);
    }
}
