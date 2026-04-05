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
use Filament\Navigation\MenuItem;

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

            // ✅ RENDER HOOK : Ajouter les liens dans la topbar
            ->renderHook(
                'panels::topbar.end',
                fn() => view('filament.decision-recours.topbar-shortcuts')
            )

            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Mon Profil')
                    ->url(fn() => '/decision-recours/profile')
                    ->icon('heroicon-o-user-circle'),

                MenuItem::make()
                    ->label('🏠 Portail Principal')
                    ->url('/portal')
                    ->icon('heroicon-o-home')
                    ->sort(10),

                MenuItem::make()
                    ->label('Administration')
                    ->url('/portal/users')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn() => auth()->user()?->hasAnyRole(['Super Administrateur', 'Administrateur']) ?? false)
                    ->sort(20),

                'logout' => MenuItem::make()
                    ->label('Déconnexion')
                    ->icon('heroicon-o-arrow-right-on-rectangle'),
            ])

            ->globalSearch(true)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
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
                \App\Modules\DecisionRecours\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/DecisionRecours/Filament/Widgets'),
                for: 'App\\Modules\\DecisionRecours\\Filament\\Widgets'
            )
            ->widgets([
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