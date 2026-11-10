<?php

namespace App\Providers;

use App\Models\MouvementSequestre;
use App\Models\User;
use App\Observers\MouvementSequestreObserver;
use App\Observers\UserObserver;
use App\Support\JournalAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Administrateur') ? true : null;
        });

        // ✅ Recalcul automatique du solde/précompte des séquestres
        MouvementSequestre::observe(MouvementSequestreObserver::class);

        User::observe(UserObserver::class);

        $this->journaliserAuthentification();
    }

    /**
     * Connexions, déconnexions et échecs de connexion : ces actions ne passent
     * par aucun modèle et doivent pourtant figurer au journal d'audit.
     */
    protected function journaliserAuthentification(): void
    {
        Event::listen(Login::class, function (Login $event) {
            JournalAudit::journaliser('authentification', 'login', [
                'email' => $event->user?->email,
                'ip' => request()->ip(),
                'navigateur' => Str::limit((string) request()->userAgent(), 180),
            ], null, $event->user);
        });

        Event::listen(Logout::class, function (Logout $event) {
            JournalAudit::journaliser('authentification', 'logout', [
                'email' => $event->user?->email,
                'ip' => request()->ip(),
            ], null, $event->user);
        });

        Event::listen(Failed::class, function (Failed $event) {
            JournalAudit::journaliser('authentification', 'login_failed', [
                'email' => $event->credentials['email'] ?? null,
                'ip' => request()->ip(),
                'navigateur' => Str::limit((string) request()->userAgent(), 180),
            ]);
        });

        Event::listen(RoleAttached::class, function (RoleAttached $event) {
            if (! $event->model instanceof User) {
                return;
            }

            JournalAudit::journaliser('utilisateurs', 'roles_attaches', [
                'roles' => JournalAudit::nomsRoles($event->rolesOrIds),
            ], $event->model);
        });

        Event::listen(RoleDetached::class, function (RoleDetached $event) {
            if (! $event->model instanceof User) {
                return;
            }

            JournalAudit::journaliser('utilisateurs', 'roles_detaches', [
                'roles' => JournalAudit::nomsRoles($event->rolesOrIds),
            ], $event->model);
        });

        Event::listen(PermissionAttached::class, function (PermissionAttached $event) {
            $journal = $event->model instanceof User ? 'utilisateurs' : 'systeme';

            JournalAudit::journaliser($journal, 'permissions_attachees', [
                'permissions' => JournalAudit::nomsPermissions($event->permissionsOrIds),
            ], $event->model);
        });

        Event::listen(PermissionDetached::class, function (PermissionDetached $event) {
            $journal = $event->model instanceof User ? 'utilisateurs' : 'systeme';

            JournalAudit::journaliser($journal, 'permissions_detachees', [
                'permissions' => JournalAudit::nomsPermissions($event->permissionsOrIds),
            ], $event->model);
        });
    }
}
