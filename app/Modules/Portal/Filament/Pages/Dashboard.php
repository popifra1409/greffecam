<?php

namespace App\Modules\Portal\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Models\ModuleAccess;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'modules.portal.dashboard';

    protected static ?string $title = 'Portail d\'Accès';

    public function getModules(): array
    {
        $user = auth()->user();
        $allModules = ModuleAccess::getAvailableModules();

        // Administrateur voit tout
        if ($user->hasRole('Administrateur')) {
            return $allModules;
        }

        // Filtrer selon les accès
        $accessibleModules = [];

        foreach ($user->roles as $role) {
            $accesses = ModuleAccess::where('role_id', $role->id)
                ->where('can_access', true)
                ->get();

            foreach ($accesses as $access) {
                if (isset($allModules[$access->module_code])) {
                    $accessibleModules[$access->module_code] = $allModules[$access->module_code];
                }
            }
        }

        return $accessibleModules;
    }
}