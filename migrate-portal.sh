#!/bin/bash
# ================================================================
# migrate-portal.sh
# Migration de User, Role, Permission vers Portal
# ================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()    { echo -e "${GREEN}✅ $1${NC}"; }
warn()   { echo -e "${YELLOW}⚠️  $1${NC}"; }
info()   { echo -e "${BLUE}ℹ️  $1${NC}"; }
header() { echo -e "\n${BLUE}══════════════════════════════════════${NC}"; echo -e "${BLUE}  $1${NC}"; echo -e "${BLUE}══════════════════════════════════════${NC}"; }

# ================================================================
# SAUVEGARDE
# ================================================================
header "Sauvegarde git"
git add -A && git commit -m "chore: avant migration Portal" --allow-empty
log "Commit créé"

# ================================================================
# CRÉATION
# ================================================================
header "Création arborescence Portal"
mkdir -p app/Modules/Portal/Filament/Resources
mkdir -p app/Modules/Portal/Filament/Pages
mkdir -p app/Modules/Portal/Filament/Widgets
log "Arborescence créée"

# ================================================================
# DÉPLACEMENT
# ================================================================
header "Déplacement User, Role, Permission"

PORTAL_RESOURCES=("UserResource" "RoleResource" "PermissionResource")

for resource in "${PORTAL_RESOURCES[@]}"; do
    if [ -f "app/Filament/Resources/${resource}.php" ]; then
        mv "app/Filament/Resources/${resource}.php" \
           "app/Modules/Portal/Filament/Resources/${resource}.php"
        log "Déplacé : ${resource}.php"
    fi

    resourceFolder="${resource//Resource/}"
    if [ -d "app/Filament/Resources/${resourceFolder}" ]; then
        mv "app/Filament/Resources/${resourceFolder}" \
           "app/Modules/Portal/Filament/Resources/${resourceFolder}"
        log "Déplacé : ${resourceFolder}/"
    fi
done

# ================================================================
# CORRECTION NAMESPACES
# ================================================================
header "Correction namespaces"

find app/Modules/Portal/Filament/Resources -maxdepth 1 -name "*.php" | while read file; do
    sed -i 's|^namespace App\\Filament\\Resources;|namespace App\\Modules\\Portal\\Filament\\Resources;|' "$file"
done

find app/Modules/Portal/Filament/Resources -mindepth 2 -name "*.php" | while read file; do
    sed -i 's|namespace App\\Filament\\Resources\\|namespace App\\Modules\\Portal\\Filament\\Resources\\|g' "$file"
    sed -i 's|use App\\Filament\\Resources\\|use App\\Modules\\Portal\\Filament\\Resources\\|g' "$file"
done

find app/Modules/Portal/Filament/Resources -maxdepth 1 -name "*Resource.php" | while read file; do
    sed -i 's|use App\\Filament\\Resources\\\([^\\]*Resource\)\\Pages;|use App\\Modules\\Portal\\Filament\\Resources\\\1\\Pages;|g' "$file"
done

find app/Modules/Portal -name "*.php" | while read file; do
    sed -i 's|\\App\\Filament\\Resources\\|\\App\\Modules\\Portal\\Filament\\Resources\\|g' "$file"
done

log "Namespaces corrigés"

# ================================================================
# COMPOSER
# ================================================================
header "Mise à jour composer.json"

cp composer.json composer.json.backup2

if command -v jq &> /dev/null; then
    jq '.autoload["psr-4"]["App\\Modules\\Portal\\"] = "app/Modules/Portal/"' composer.json > composer.json.tmp
    mv composer.json.tmp composer.json
    log "Namespace ajouté"
else
    warn "Ajoutez : \"App\\\\Modules\\\\Portal\\\\\": \"app/Modules/Portal/\","
    read -p "Appuyez sur Entrée..."
fi

composer dump-autoload
log "Autoload régénéré"

# ================================================================
# PANEL PROVIDER
# ================================================================
header "Création Panel Portal"

php artisan make:filament-panel portal --force 2>/dev/null || true

cat > app/Providers/Filament/PortalPanelProvider.php << 'EOF'
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

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('portal')
            ->path('portal')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(
                in: app_path('Modules/Portal/Filament/Resources'),
                for: 'App\\Modules\\Portal\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Modules/Portal/Filament/Pages'),
                for: 'App\\Modules\\Portal\\Filament\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/Portal/Filament/Widgets'),
                for: 'App\\Modules\\Portal\\Filament\\Widgets'
            )
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
            ->brandName('Administration')
            ->navigationGroups([
                'Administration',
            ]);
    }
}
EOF

log "Panel Provider créé"

# ================================================================
# NETTOYAGE
# ================================================================
header "Nettoyage"
php artisan optimize:clear
php artisan filament:cache-clear 2>/dev/null || true
log "Caches vidés"

# ================================================================
# RÉSUMÉ
# ================================================================
header "✅ MIGRATION PORTAL TERMINÉE"
echo -e "${GREEN}http://localhost:8000/portal → Administration${NC}"
echo -e "${GREEN}http://localhost:8000/decision-recours → Application${NC}"