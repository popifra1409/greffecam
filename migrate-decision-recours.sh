#!/bin/bash
# ================================================================
# migrate-decision-recours.sh
# Migration de TOUTES les resources SAUF administration
# ================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log()    { echo -e "${GREEN}✅ $1${NC}"; }
warn()   { echo -e "${YELLOW}⚠️  $1${NC}"; }
error()  { echo -e "${RED}❌ $1${NC}"; exit 1; }
info()   { echo -e "${BLUE}ℹ️  $1${NC}"; }
header() { echo -e "\n${BLUE}══════════════════════════════════════${NC}"; echo -e "${BLUE}  $1${NC}"; echo -e "${BLUE}══════════════════════════════════════${NC}"; }

[ -f "artisan" ] || error "Lancez depuis la racine du projet"

# ================================================================
# ÉTAPE 0 — SAUVEGARDE GIT
# ================================================================
header "ÉTAPE 0 — Sauvegarde git"

git add -A && git commit -m "chore: avant migration DecisionRecours" --allow-empty
log "Commit de sauvegarde créé"

# ================================================================
# ÉTAPE 1 — CRÉER L'ARBORESCENCE
# ================================================================
header "ÉTAPE 1 — Création arborescence"

mkdir -p app/Modules/DecisionRecours/Filament/Resources
mkdir -p app/Modules/DecisionRecours/Filament/Pages
mkdir -p app/Modules/DecisionRecours/Filament/Widgets
log "Arborescence créée"

# ================================================================
# ÉTAPE 2 — DÉPLACER LES RESOURCES (TOUT SAUF ADMIN)
# ================================================================
header "ÉTAPE 2 — Déplacement des Resources"

DECISION_RECOURS_RESOURCES=(
    "DossierResource"
    "DecisionResource"
    "RecoursResource"
    "AlerteRecoursResource"
    "TransmissionDecisionResource"
    "TribunalResource"
    "SectionResource"
    "MatiereResource"
    "InfractionResource"
    "NatureDecisionResource"
    "TypeDecisionResource"
    "CategorieDecisionResource"
    "JugeResource"
    "CollegeJugeResource"
    "GreffierResource"
    "AnneeJudiciaireResource"
    "JourFerieResource"
    "TypeRecoursResource"
)

for resource in "${DECISION_RECOURS_RESOURCES[@]}"; do
    if [ -f "app/Filament/Resources/${resource}.php" ]; then
        mv "app/Filament/Resources/${resource}.php" \
           "app/Modules/DecisionRecours/Filament/Resources/${resource}.php"
        log "Déplacé : ${resource}.php"
    else
        warn "Introuvable : ${resource}.php"
    fi

    resourceFolder="${resource//Resource/}"
    if [ -d "app/Filament/Resources/${resourceFolder}" ]; then
        mv "app/Filament/Resources/${resourceFolder}" \
           "app/Modules/DecisionRecours/Filament/Resources/${resourceFolder}"
        log "Déplacé : ${resourceFolder}/"
    fi
done

# ================================================================
# ÉTAPE 3 — CORRECTION DES NAMESPACES
# ================================================================
header "ÉTAPE 3 — Correction des namespaces"

# 3a. Fichiers principaux
find app/Modules/DecisionRecours/Filament/Resources -maxdepth 1 -name "*.php" | while read file; do
    sed -i 's|^namespace App\\Filament\\Resources;|namespace App\\Modules\\DecisionRecours\\Filament\\Resources;|' "$file"
done
log "Namespaces principaux corrigés"

# 3b. Sous-dossiers (Pages, RelationManagers)
find app/Modules/DecisionRecours/Filament/Resources -mindepth 2 -name "*.php" | while read file; do
    sed -i 's|namespace App\\Filament\\Resources\\|namespace App\\Modules\\DecisionRecours\\Filament\\Resources\\|g' "$file"
    sed -i 's|use App\\Filament\\Resources\\|use App\\Modules\\DecisionRecours\\Filament\\Resources\\|g' "$file"
done
log "Namespaces sous-dossiers corrigés"

# 3c. Corriger les use statements dans les fichiers principaux
find app/Modules/DecisionRecours/Filament/Resources -maxdepth 1 -name "*Resource.php" | while read file; do
    sed -i 's|use App\\Filament\\Resources\\\([^\\]*Resource\)\\Pages;|use App\\Modules\\DecisionRecours\\Filament\\Resources\\\1\\Pages;|g' "$file"
    sed -i 's|use App\\Filament\\Resources\\\([^\\]*Resource\)\\RelationManagers;|use App\\Modules\\DecisionRecours\\Filament\\Resources\\\1\\RelationManagers;|g' "$file"
done
log "Use statements corrigés"

# 3d. Corriger les namespaces complets
find app/Modules/DecisionRecours -name "*.php" | while read file; do
    sed -i 's|\\App\\Filament\\Resources\\|\\App\\Modules\\DecisionRecours\\Filament\\Resources\\|g' "$file"
done
log "Namespaces complets corrigés"

# 3e. Supprimer les use RelationManagers inutiles
find app/Modules/DecisionRecours/Filament/Resources -path "*/RelationManagers/*.php" | while read file; do
    sed -i '/^use App\\Filament\\Resources\\.*\\RelationManagers;$/d' "$file"
    sed -i '/^use App\\Modules\\DecisionRecours\\Filament\\Resources\\.*\\RelationManagers;$/d' "$file"
done
log "Use statements inutiles supprimés"

# ================================================================
# ÉTAPE 4 — MISE À JOUR COMPOSER.JSON
# ================================================================
header "ÉTAPE 4 — Mise à jour composer.json"

cp composer.json composer.json.backup

# Utiliser jq si disponible, sinon manuel
if command -v jq &> /dev/null; then
    jq '.autoload["psr-4"]["App\\Modules\\DecisionRecours\\"] = "app/Modules/DecisionRecours/"' composer.json > composer.json.tmp
    mv composer.json.tmp composer.json
    log "Namespace ajouté à composer.json"
else
    warn "Ajoutez manuellement dans composer.json, section autoload.psr-4 :"
    echo '  "App\\Modules\\DecisionRecours\\": "app/Modules/DecisionRecours/",'
    read -p "Appuyez sur Entrée après modification..."
fi

composer dump-autoload
log "Autoload régénéré"

# ================================================================
# ÉTAPE 5 — CRÉER LE PANEL PROVIDER
# ================================================================
header "ÉTAPE 5 — Création du Panel Provider"

php artisan make:filament-panel decision-recours --force 2>/dev/null || true

cat > app/Providers/Filament/DecisionRecoursPanelProvider.php << 'EOF'
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
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/DecisionRecours/Filament/Widgets'),
                for: 'App\\Modules\\DecisionRecours\\Filament\\Widgets'
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
            ->brandName('Décision & Recours')
            ->navigationGroups([
                'Gestion Judiciaire',
                'Référentiels',
                'Paramétrage',
            ]);
    }
}
EOF

log "Panel Provider créé"

# ================================================================
# ÉTAPE 6 — NETTOYAGE
# ================================================================
header "ÉTAPE 6 — Nettoyage"

php artisan optimize:clear
php artisan filament:cache-clear 2>/dev/null || true
log "Caches vidés"

# ================================================================
# RÉSUMÉ
# ================================================================
header "✅ MIGRATION DÉCISION RECOURS TERMINÉE"

RESOURCES_COUNT=$(find app/Modules/DecisionRecours/Filament/Resources -maxdepth 1 -name "*.php" | wc -l)

echo -e "${GREEN}Resources migrées : ${RESOURCES_COUNT} fichiers${NC}"
echo -e "${GREEN}Restent dans admin : User, Role, Permission${NC}"
echo ""
echo -e "${YELLOW}PROCHAINES ÉTAPES :${NC}"
echo "1. php artisan serve"
echo "2. http://localhost:8000/decision-recours"
echo "3. Exécutez migrate-portal.sh pour l'administration"
echo ""