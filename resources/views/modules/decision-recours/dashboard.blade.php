<x-filament-panels::page>
    {{-- En-tête personnalisé (optionnel) --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            Gestion Judiciaire - Décision & Recours
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Vue d'ensemble de l'activité judiciaire
        </p>
    </div>

    {{-- Les widgets seront rendus automatiquement ici --}}
    <x-filament-widgets::widgets :widgets="$this->getWidgets()" :columns="$this->getColumns()" />
</x-filament-panels::page>