<div class="flex items-center gap-3 mr-4">
    {{-- Retour au portail --}}
    <a href="/portal"
        class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        title="Retour au portail principal">
        <x-heroicon-o-arrow-left-circle class="w-5 h-5" />
        <span class="hidden md:inline">Portail</span>
    </a>

    <span class="text-gray-300 dark:text-gray-600">|</span>

    {{-- Nouveau Dossier --}}
    <a href="{{ route('filament.decision-recours.resources.dossiers.create') }}"
        class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition"
        title="Créer un nouveau dossier">
        <x-heroicon-o-folder-plus class="w-5 h-5" />
        <span class="hidden md:inline">Nouveau Dossier</span>
    </a>

    {{-- Nouvelle Décision --}}
    <a href="{{ route('filament.decision-recours.resources.decisions.create') }}"
        class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition"
        title="Créer une nouvelle décision">
        <x-heroicon-o-document-plus class="w-5 h-5" />
        <span class="hidden md:inline">Nouvelle Décision</span>
    </a>

    <span class="text-gray-300 dark:text-gray-600 hidden lg:inline">|</span>

    {{-- Compteur Recours en attente --}}
    @php
        $recoursEnAttente = \App\Models\Recours::whereNull('date_transmission_cour_appel')->count();
    @endphp

    @if($recoursEnAttente > 0)
        <a href="/decision-recours/recours"
            class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition hidden lg:flex"
            title="Recours en attente de transmission">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            <span class="hidden xl:inline">Recours en attente</span>
            <span
                class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-orange-600 rounded-full">
                {{ $recoursEnAttente }}
            </span>
        </a>
    @endif
</div>