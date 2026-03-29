<x-filament-panels::page>
    <div class="space-y-6">
        {{-- En-tête --}}
        <div class="text-center py-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                Bienvenue, {{ auth()->user()->name }}
            </h2>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">
                Sélectionnez un module pour commencer
            </p>
        </div>

        {{-- Grille de modules --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->getModules() as $code => $module)
                <a href="{{ $module['url'] }}"
                    class="relative block p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md hover:shadow-xl transition-all duration-200 border-2 {{ isset($module['available']) && !$module['available'] ? 'border-gray-300 dark:border-gray-700 opacity-70 cursor-not-allowed' : 'border-gray-200 dark:border-gray-800 hover:border-' . $module['color'] . '-500' }} group"
                    @if(isset($module['available']) && !$module['available'])
                    onclick="event.preventDefault(); alert('Ce module sera bientôt disponible');" @endif>
                    {{-- Badge "Bientôt disponible" --}}
                    @if(isset($module['available']) && !$module['available'])
                        <div class="absolute top-4 right-4">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Bientôt disponible
                            </span>
                        </div>
                    @endif

                    {{-- Icône --}}
                    <div class="flex items-center mb-4">
                        <div
                            class="flex items-center justify-center w-14 h-14 rounded-xl bg-{{ $module['color'] }}-100 dark:bg-{{ $module['color'] }}-900/30 group-hover:scale-110 transition-transform">
                            <x-filament::icon :icon="$module['icon']"
                                class="w-7 h-7 text-{{ $module['color'] }}-600 dark:text-{{ $module['color'] }}-400" />
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $module['name'] }}
                        </h3>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                        {{ $module['description'] }}
                    </p>

                    {{-- Fonctionnalités --}}
                    <ul class="space-y-2 mb-6">
                        @foreach($module['features'] as $feature)
                            <li class="flex items-start text-sm text-gray-700 dark:text-gray-200">
                                <svg class="w-5 h-5 mt-0.5 mr-2 text-{{ $module['color'] }}-500 dark:text-{{ $module['color'] }}-400 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Bouton d'accès --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span
                            class="text-sm font-semibold text-{{ $module['color'] }}-600 dark:text-{{ $module['color'] }}-400 group-hover:text-{{ $module['color'] }}-700 dark:group-hover:text-{{ $module['color'] }}-300 transition-colors">
                            {{ isset($module['available']) && !$module['available'] ? 'Bientôt disponible' : 'Accéder au module' }}
                        </span>
                        @if(!isset($module['available']) || $module['available'])
                            <svg class="w-5 h-5 text-{{ $module['color'] }}-600 dark:text-{{ $module['color'] }}-400 group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Bloc Administration (visible seulement pour les admins) --}}
        @if(auth()->user()->hasRole('Administrateur'))
            <div class="mt-8 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-800">
                <div class="flex items-center mb-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/40">
                        <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="ml-3 text-lg font-bold text-gray-900 dark:text-gray-100">
                        Administration
                    </h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    Gestion des utilisateurs, rôles et permissions du système
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="/portal/users"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium text-sm rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Utilisateurs
                    </a>
                    <a href="/portal/roles"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium text-sm rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Rôles
                    </a>
                    <a href="/portal/permissions"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium text-sm rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Permissions
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>