<x-filament-panels::page>
    @php
        $couleurs = [
            'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'bgIcon' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'hover:border-amber-400 dark:hover:border-amber-500', 'accent' => 'bg-amber-500', 'btn' => 'bg-amber-600 hover:bg-amber-700'],
            'green' => ['bg' => 'bg-green-50 dark:bg-green-900/20', 'bgIcon' => 'bg-green-100 dark:bg-green-900/40', 'text' => 'text-green-600 dark:text-green-400', 'border' => 'hover:border-green-400 dark:hover:border-green-500', 'accent' => 'bg-green-500', 'btn' => 'bg-green-600 hover:bg-green-700'],
            'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'bgIcon' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'hover:border-blue-400 dark:hover:border-blue-500', 'accent' => 'bg-blue-500', 'btn' => 'bg-blue-600 hover:bg-blue-700'],
            'gray' => ['bg' => 'bg-gray-50 dark:bg-gray-800/40', 'bgIcon' => 'bg-gray-200 dark:bg-gray-700', 'text' => 'text-gray-500 dark:text-gray-400', 'border' => 'hover:border-gray-400 dark:hover:border-gray-500', 'accent' => 'bg-gray-400', 'btn' => 'bg-gray-500 hover:bg-gray-600'],
        ];
        $modules = $this->getModules();
    @endphp

    <div class="space-y-6">

        {{-- ================= EN-TÊTE ================= --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-6 text-white shadow-lg">
            <div class="relative z-10 flex flex-col gap-1">
                <p class="text-xs font-medium uppercase tracking-wide text-white/70">
                    {{ now()->translatedFormat('l d F Y') }}
                </p>
                <h2 class="text-2xl font-bold text-white">
                    Bonjour, {{ explode(' ', auth()->user()->name)[0] }} 👋
                </h2>
                <p class="text-sm text-white/80 max-w-xl">
                    Sélectionnez un module ci-dessous pour accéder à vos outils de gestion.
                </p>
            </div>

            {{-- Motif décoratif --}}
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -right-20 bottom-0 h-28 w-28 rounded-full bg-white/5"></div>

            {{-- Mini-stat --}}
            <div class="relative z-10 mt-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                <x-filament::icon icon="heroicon-o-squares-2x2" class="w-3.5 h-3.5" />
                {{ $this->getNombreModulesActifs() }} module{{ $this->getNombreModulesActifs() > 1 ? 's' : '' }} disponible{{ $this->getNombreModulesActifs() > 1 ? 's' : '' }}
            </div>
        </div>

        {{-- ================= GRILLE DE MODULES ================= --}}
        @if(count($modules) === 0 && !$this->estAdministrateur())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <x-filament::icon icon="heroicon-o-lock-closed" class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" />
                <p class="text-gray-500 dark:text-gray-400">
                    Aucun module ne vous a encore été attribué. Contactez un administrateur.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($modules as $code => $module)
                    @php
                        $c = $couleurs[$module['color']] ?? $couleurs['gray'];
                        $disponible = !isset($module['available']) || $module['available'];
                    @endphp

                    <a href="{{ $disponible ? $module['url'] : '#' }}"
                        @if(!$disponible) onclick="event.preventDefault();" @endif
                        class="group relative flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition-all duration-200 {{ $disponible ? $c['border'] . ' hover:shadow-md hover:-translate-y-0.5' : 'opacity-60 cursor-not-allowed' }}">

                        {{-- Bandeau coloré supérieur --}}
                        <div class="h-1 w-full {{ $c['accent'] }}"></div>

                        <div class="flex flex-1 flex-col p-5">
                            {{-- En-tête carte --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center justify-center w-12 h-12 rounded-lg {{ $c['bgIcon'] }} transition-transform group-hover:scale-105">
                                    <x-filament::icon :icon="$module['icon']" class="w-6 h-6 {{ $c['text'] }}" />
                                </div>

                                @if(!$disponible)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                        Bientôt
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {{ $module['name'] }}
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 leading-relaxed">
                                {{ $module['description'] }}
                            </p>

                            {{-- Fonctionnalités --}}
                            <ul class="space-y-1 mb-4 flex-1">
                                @foreach(array_slice($module['features'], 0, 4) as $feature)
                                    <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                        <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 mt-0.5 {{ $c['text'] }} flex-shrink-0" />
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Pied de carte --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-sm font-semibold {{ $c['text'] }}">
                                    {{ $disponible ? 'Accéder au module' : 'Indisponible pour le moment' }}
                                </span>
                                @if($disponible)
                                    <x-filament::icon icon="heroicon-o-arrow-right" class="w-5 h-5 {{ $c['text'] }} transition-transform group-hover:translate-x-1" />
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach

                {{-- ================= CARTE ADMINISTRATION (intégrée au grid) ================= --}}
                @if($this->estAdministrateur())
                    @php $ca = $couleurs['gray']; @endphp
                    <div class="group relative flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                        <div class="h-1 w-full {{ $ca['accent'] }}"></div>

                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg {{ $ca['bgIcon'] }} mb-3">
                                <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-6 h-6 {{ $ca['text'] }}" />
                            </div>

                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">
                                Administration
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                                Gestion des utilisateurs, rôles et permissions du système.
                            </p>

                            <div class="mt-auto flex flex-wrap gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <a href="/portal/users" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white {{ $ca['btn'] }} transition-colors">
                                    <x-filament::icon icon="heroicon-o-users" class="w-3.5 h-3.5" />
                                    Utilisateurs
                                </a>
                                <a href="/portal/roles" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white {{ $ca['btn'] }} transition-colors">
                                    <x-filament::icon icon="heroicon-o-shield-check" class="w-3.5 h-3.5" />
                                    Rôles
                                </a>
                                <a href="/portal/permissions" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white {{ $ca['btn'] }} transition-colors">
                                    <x-filament::icon icon="heroicon-o-key" class="w-3.5 h-3.5" />
                                    Permissions
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>