<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Chaque archive est un fichier <strong>JSONL compressé (gzip)</strong> contenant une ligne par entrée,
            classée par mois. Téléchargez le fichier pour le conserver hors de la base de données.
        </p>
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>
