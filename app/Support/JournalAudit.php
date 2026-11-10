<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

/**
 * Libellés et conventions partagés par la journalisation (modèles, événements)
 * et par les écrans de consultation du journal.
 */
class JournalAudit
{
    /**
     * Colonnes techniques ou sensibles qui ne doivent jamais figurer dans le
     * journal : horodatages, secrets, traces de connexion (bruit inutile).
     */
    public const CHAMPS_EXCLUS = [
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
        'remember_token',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
    ];

    /**
     * Noms de journaux par défaut, dérivés du nom de table.
     */
    private const TABLES_VERS_JOURNAUX = [
        'users' => 'utilisateurs',
        'tribunals' => 'tribunaux',
        'annee_judiciaires' => 'annees_judiciaires',
    ];

    public const LABELS_JOURNAUX = [
        'authentification' => 'Authentification',
        'rapports' => 'Rapports',
        'systeme' => 'Système',
        'documents' => 'Documents',
        'default' => 'Général',
        'dossiers' => 'Dossiers',
        'dossier_parties' => 'Parties de dossier',
        'parties' => 'Parties',
        'decisions' => 'Décisions',
        'categorie_decisions' => 'Catégories de décision',
        'nature_decisions' => 'Natures de décision',
        'type_decisions' => 'Types de décision',
        'recours' => 'Recours',
        'acte_recours' => 'Actes de recours',
        'etape_recours' => 'Étapes de recours',
        'type_recours' => 'Types de recours',
        'transmission_decisions' => 'Transmissions',
        'sequestres' => 'Séquestres',
        'mouvements_sequestre' => 'Mouvements de séquestre',
        'sequestre_ayants_droits' => 'Ayants droit',
        'sequestre_parties_adverses' => 'Parties adverses',
        'sequestre_parties_tierces' => 'Parties tierces',
        'sequestre_documents' => 'Documents de séquestre',
        'nature_sequestres' => 'Natures de séquestre',
        'statut_sequestres' => 'Statuts de séquestre',
        'motifs_mouvements' => 'Motifs de mouvement',
        'banque_releves' => 'Relevés bancaires',
        'utilisateurs' => 'Utilisateurs',
        'greffiers' => 'Greffiers',
        'juges' => 'Juges',
        'grades' => 'Grades',
        'tribunaux' => 'Tribunaux',
        'sections' => 'Sections',
        'matieres' => 'Matières',
        'infractions' => 'Infractions',
        'jour_feries' => 'Jours fériés',
        'annees_judiciaires' => 'Années judiciaires',
        'type_documents' => 'Types de document',
        'college_juges' => 'Collèges de juges',
        'notification_preferences' => 'Préférences de notification',
        'module_accesses' => 'Accès aux modules',
    ];

    private const LABELS_SUJETS = [
        'Dossier' => 'Dossier',
        'Decision' => 'Décision',
        'Recours' => 'Recours',
        'ActeRecours' => 'Acte de recours',
        'EtapeRecours' => 'Étape de recours',
        'TransmissionDecision' => 'Transmission',
        'Sequestre' => 'Séquestre',
        'MouvementSequestre' => 'Mouvement',
        'SequestreAyantDroit' => 'Ayant droit',
        'SequestrePartieAdverse' => 'Partie adverse',
        'SequestrePartieTierce' => 'Partie tierce',
        'SequestreDocument' => 'Document de séquestre',
        'BanqueReleve' => 'Relevé bancaire',
        'User' => 'Utilisateur',
        'Greffier' => 'Greffier',
        'Juge' => 'Juge',
        'Tribunal' => 'Tribunal',
        'Section' => 'Section',
        'Matiere' => 'Matière',
        'Grade' => 'Grade',
        'AuditArchive' => 'Archive du journal',
        'Role' => 'Rôle',
    ];

    /**
     * Options de journalisation par défaut d'un modèle.
     */
    public static function options(string $table): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(self::CHAMPS_EXCLUS)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(self::nomJournal($table))
            ->setDescriptionForEvent(fn(string $event) => self::libelleEvenement($event));
    }

    public static function nomJournal(string $table): string
    {
        return self::TABLES_VERS_JOURNAUX[$table] ?? $table;
    }

    public static function libelleJournal(?string $journal): string
    {
        if (blank($journal)) {
            return self::LABELS_JOURNAUX['default'];
        }

        return self::LABELS_JOURNAUX[$journal]
            ?? Str::of($journal)->replace('_', ' ')->ucfirst()->toString();
    }

    public static function libelleEvenement(?string $evenement): string
    {
        return match ($evenement) {
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'restored' => 'Restauration',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'login_failed' => 'Échec de connexion',
            'password_updated' => 'Mot de passe modifié',
            'roles_attaches' => 'Attribution de rôles',
            'roles_detaches' => 'Retrait de rôles',
            'sections_modifiees' => 'Modification des sections',
            'permissions_attachees' => 'Attribution de permissions',
            'permissions_detachees' => 'Retrait de permissions',
            'pdf_genere' => 'Génération de PDF',
            'document_telecharge' => 'Téléchargement de document',
            'archive_telechargee' => 'Téléchargement d\'archive',
            'archivage' => 'Archivage',
            default => blank($evenement)
                ? '—'
                : Str::of($evenement)->replace('_', ' ')->ucfirst()->toString(),
        };
    }

    public static function couleurEvenement(?string $evenement): string
    {
        return match (true) {
            in_array($evenement, ['created', 'login', 'restored'], true) => 'success',
            in_array($evenement, ['updated', 'password_updated', 'roles_attaches', 'roles_detaches', 'permissions_attachees', 'permissions_detachees', 'sections_modifiees'], true) => 'warning',
            in_array($evenement, ['deleted', 'login_failed'], true) => 'danger',
            in_array($evenement, ['pdf_genere', 'document_telecharge', 'archive_telechargee'], true) => 'info',
            default => 'gray',
        };
    }

    public static function libelleSujet(Activity $activite): string
    {
        if (blank($activite->subject_type)) {
            return '—';
        }

        $classe = class_basename($activite->subject_type);
        $label = self::LABELS_SUJETS[$classe] ?? Str::of($classe)->snake(' ')->ucfirst()->toString();
        $sujet = $activite->relationLoaded('subject') ? $activite->subject : null;

        $identifiant = match (true) {
            $sujet instanceof \App\Models\Dossier => $sujet->numero_dossier,
            $sujet instanceof \App\Models\Decision => $sujet->numero_repertoire,
            $sujet instanceof \App\Models\Sequestre => $sujet->numero_dossier_sequestre,
            $sujet instanceof User => $sujet->name,
            $sujet instanceof \App\Models\Greffier => trim(($sujet->matricule ?? '') . ' — ' . $sujet->nom_complet, ' —'),
            $sujet instanceof Model => $sujet->getAttribute('libelle')
                ?? $sujet->getAttribute('nom')
                ?? $sujet->getAttribute('intitule')
                ?? $sujet->getAttribute('numero_dossier')
                ?? '#' . $activite->subject_id,
            default => '#' . $activite->subject_id,
        };

        return $label . ' · ' . ($identifiant ?: '#' . $activite->subject_id);
    }

    public static function valeurLisible(mixed $valeur): string
    {
        return match (true) {
            $valeur === null => '—',
            is_bool($valeur) => $valeur ? 'Oui' : 'Non',
            is_array($valeur) => json_encode($valeur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $valeur,
        };
    }

    public static function peutConsulter(?User $utilisateur): bool
    {
        return $utilisateur !== null
            && ($utilisateur->can('view_audit_logs')
                || $utilisateur->hasRole(['Super Administrateur', 'Administrateur']));
    }

    /**
     * Enregistre une entrée libre (authentification, rapport, archivage…).
     */
    public static function journaliser(
        string $journal,
        string $evenement,
        array $proprietes = [],
        ?Model $sujet = null,
        ?Authenticatable $auteur = null,
    ): void {
        $logger = activity($journal)
            ->event($evenement)
            ->withProperties($proprietes);

        if ($sujet !== null) {
            $logger->performedOn($sujet);
        }

        if ($auteur !== null) {
            $logger->causedBy($auteur);
        }

        $logger->log(self::libelleEvenement($evenement));
    }

    /**
     * @param  array|int[]|string[]|\Spatie\Permission\Contracts\Role|\Spatie\Permission\Contracts\Role[]|\Illuminate\Support\Collection  $rolesOuIds
     */
    public static function nomsRoles(mixed $rolesOuIds): array
    {
        if ($rolesOuIds instanceof \Spatie\Permission\Contracts\Role) {
            return [$rolesOuIds->name];
        }

        $valeurs = $rolesOuIds instanceof \Illuminate\Support\Collection
            ? $rolesOuIds->all()
            : (array) $rolesOuIds;

        if ($valeurs === []) {
            return [];
        }

        if ($valeurs[0] instanceof \Spatie\Permission\Contracts\Role) {
            return array_map(fn($role) => $role->name, $valeurs);
        }

        return \Spatie\Permission\Models\Role::query()
            ->whereIn('id', $valeurs)
            ->pluck('name')
            ->all();
    }

    /**
     * @param  array|int[]|string[]|\Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Permission[]|\Illuminate\Support\Collection  $permissionsOuIds
     */
    public static function nomsPermissions(mixed $permissionsOuIds): array
    {
        if ($permissionsOuIds instanceof \Spatie\Permission\Contracts\Permission) {
            return [$permissionsOuIds->name];
        }

        $valeurs = $permissionsOuIds instanceof \Illuminate\Support\Collection
            ? $permissionsOuIds->all()
            : (array) $permissionsOuIds;

        if ($valeurs === []) {
            return [];
        }

        if ($valeurs[0] instanceof \Spatie\Permission\Contracts\Permission) {
            return array_map(fn($permission) => $permission->name, $valeurs);
        }

        return \Spatie\Permission\Models\Permission::query()
            ->whereIn('id', $valeurs)
            ->pluck('name')
            ->all();
    }
}
