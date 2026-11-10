<div class="space-y-4 text-sm">
    @php
        $proprietes = $activite->properties?->toArray() ?? [];
        $anciens = $proprietes['old'] ?? [];
        $nouveaux = $proprietes['attributes'] ?? [];
        $champs = array_values(array_unique(array_merge(array_keys($anciens), array_keys($nouveaux))));
        $autres = collect($proprietes)
            ->except(['old', 'attributes'])
            ->filter(fn($valeur) => filled($valeur))
            ->all();
    @endphp

    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Date</dt>
            <dd class="font-medium">{{ $activite->created_at?->format('d/m/Y à H:i:s') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Auteur</dt>
            <dd class="font-medium">{{ $activite->causer?->name ?? 'Système' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Journal</dt>
            <dd class="font-medium">{{ \App\Support\JournalAudit::libelleJournal($activite->log_name) }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Action</dt>
            <dd class="font-medium">{{ \App\Support\JournalAudit::libelleEvenement($activite->event) }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-gray-500 dark:text-gray-400">Élément concerné</dt>
            <dd class="font-medium">{{ \App\Support\JournalAudit::libelleSujet($activite) }}</dd>
        </div>
    </dl>

    @if($champs !== [])
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-3 py-2 font-semibold">Champ</th>
                        <th class="px-3 py-2 font-semibold">Ancienne valeur</th>
                        <th class="px-3 py-2 font-semibold">Nouvelle valeur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($champs as $champ)
                        <tr class="border-t border-gray-100 dark:border-white/10">
                            <td class="px-3 py-2 font-mono">{{ $champ }}</td>
                            <td class="px-3 py-2">{{ \App\Support\JournalAudit::valeurLisible($anciens[$champ] ?? null) }}</td>
                            <td class="px-3 py-2">{{ \App\Support\JournalAudit::valeurLisible($nouveaux[$champ] ?? null) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($autres !== [])
        <div>
            <div class="mb-1 text-gray-500 dark:text-gray-400">Informations complémentaires</div>
            <ul class="list-inside list-disc space-y-1">
                @foreach($autres as $cle => $valeur)
                    <li><span class="font-mono">{{ $cle }}</span> : {{ \App\Support\JournalAudit::valeurLisible($valeur) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <div class="mb-1 text-gray-500 dark:text-gray-400">Description enregistrée</div>
        <div>{{ $activite->description }}</div>
    </div>
</div>
