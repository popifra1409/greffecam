<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;

class SequestreCautionPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sequestre-caution')
            ->path('sequestre-caution')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])

            ->navigationItems([
                NavigationItem::make('Portail Principal')
                    ->url('/portal')
                    ->icon('heroicon-o-arrow-left-circle')
                    ->group('Navigation')
                    ->sort(-1000),
            ])

            ->renderHook(
                'panels::topbar.end',
                fn() => view('filament.sequestre-caution.topbar-shortcuts')
            )

            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Mon Profil')
                    ->icon('heroicon-o-user-circle'),

                MenuItem::make()
                    ->label('🏠 Portail Principal')
                    ->url('/portal')
                    ->icon('heroicon-o-home')
                    ->sort(10),

                'logout' => MenuItem::make()
                    ->label('Déconnexion')
                    ->icon('heroicon-o-arrow-right-on-rectangle'),
            ])

            ->globalSearch(true)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->sidebarCollapsibleOnDesktop()

            ->discoverResources(
                in: app_path('Modules/SequestreCaution/Filament/Resources'),
                for: 'App\\Modules\\SequestreCaution\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Modules/SequestreCaution/Filament/Pages'),
                for: 'App\\Modules\\SequestreCaution\\Filament\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/SequestreCaution/Filament/Widgets'),
                for: 'App\\Modules\\SequestreCaution\\Filament\\Widgets'
            )
            ->widgets([
                // Widgets\AccountWidget::class,
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

            ->brandName('Séquestre & Caution')

            ->navigationGroups([
                'Gestion des Séquestres',
                'Référentiels',
            ]);
    }
}
