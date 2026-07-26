<?php

namespace App\Providers;

use App\Models\MouvementSequestre;
use App\Observers\MouvementSequestreObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Administrateur') ? true : null;
        });

        // ✅ Recalcul automatique du solde/précompte des séquestres
        MouvementSequestre::observe(MouvementSequestreObserver::class);
    }
}
