<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DecisionRecoursPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('decision-recours')
            ->path('decision-recours')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(
                in: app_path('Modules/DecisionRecours/Filament/Resources'),
                for: 'App\\Modules\\DecisionRecours\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Modules/DecisionRecours/Filament/Pages'),
                for: 'App\\Modules\\DecisionRecours\\Filament\\Pages'
            )
            ->pages([
                \App\Modules\DecisionRecours\Filament\Pages\Dashboard::class, // ✅ Dashboard personnalisé
            ])
            ->discoverWidgets(
                in: app_path('Modules/DecisionRecours/Filament/Widgets'),
                for: 'App\\Modules\\DecisionRecours\\Filament\\Widgets'
            )
            ->widgets([
                    // Les widgets sont chargés automatiquement par le Dashboard
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName('Décision & Recours')
            ->navigationGroups([
                'Gestion Judiciaire',
                'Référentiels',
                'Paramétrage',
            ]);
    }
}