<x-filament-panels::page>
    @php
        $stats = $this->getStatistiques();
    @endphp

    <x-filament::section>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Entrées actives</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total'], 0, ',', ' ') }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Plus ancienne entrée</div>
                <div class="text-2xl font-bold">
                    {{ $stats['plus_ancienne'] ? \Carbon\Carbon::parse($stats['plus_ancienne'])->format('d/m/Y') : '—' }}
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Conservation avant archivage</div>
                <div class="text-2xl font-bold">{{ $stats['retention'] }} mois</div>
            </div>
        </div>

        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Les entrées créées, modifiées ou supprimées dans l'application sont enregistrées automatiquement.
            L'archivage mensuel déplace les entrées les plus anciennes vers un fichier compressé,
            téléchargeable depuis « Archives du journal ».
        </p>
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>
