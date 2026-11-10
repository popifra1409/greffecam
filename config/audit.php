<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Conservation du journal actif
    |--------------------------------------------------------------------------
    | Les entrées plus anciennes sont déplacées vers une archive compressée
    | puis supprimées de la table activity_log (qui reste ainsi légère).
    */
    'retention_mois' => (int) env('AUDIT_RETENTION_MOIS', 6),

    /*
    |--------------------------------------------------------------------------
    | Dossier des archives
    |--------------------------------------------------------------------------
    | Un fichier JSONL compressé par mois : journal-audit-AAAA-MM-*.jsonl.gz
    */
    'archives_path' => storage_path('app/private/audit-archives'),

    /*
    |--------------------------------------------------------------------------
    | Taille des lots
    |--------------------------------------------------------------------------
    | Nombre d'entrées lues par itération pendant l'archivage.
    */
    'chunk' => (int) env('AUDIT_ARCHIVE_CHUNK', 1000),
];
