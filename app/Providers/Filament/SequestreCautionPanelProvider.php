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
use Hasnayeen\Themes\ThemesPlugin;
use Hasnayeen\Themes\Http\Middleware\SetTheme;

class SequestreCautionPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sequestre-caution')
            ->path('sequestre-caution')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Emerald,
            ])

            ->renderHook(
                'panels::topbar.end',
                fn() => view('filament.sequestre-caution.topbar-shortcuts')
            )

            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Mon profil')
                    ->icon('heroicon-o-user-circle'),

                MenuItem::make()
                    ->label('🏠 Portail principal')
                    ->url('/portal')
                    ->icon('heroicon-o-home'),

                MenuItem::make()
                    ->label('Gestion des utilisateurs')
                    ->url('/portal/users')
                    ->icon('heroicon-o-users')
                    ->visible(fn() => auth()->user()?->hasAnyRole(['Super Administrateur', 'Administrateur']) ?? false),

                'logout' => MenuItem::make()
                    ->label('Déconnexion')
                    ->icon('heroicon-o-arrow-right-on-rectangle'),
            ])

            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->sidebarCollapsibleOnDesktop()

            ->plugin(
                ThemesPlugin::make()
                    ->canViewThemesPage(fn() => auth()->user()?->hasRole(['Super Administrateur', 'Administrateur']) ?? false)
            )

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
                SetTheme::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->brandName('Séquestre & Caution')

            ->navigationGroups(['Séquestres', 'Banque', 'Rapports', 'Référentiels']);
    }
}
