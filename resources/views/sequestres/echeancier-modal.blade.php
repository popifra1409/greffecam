<div class="fi-modal-content space-y-4">
    <div class="grid grid-cols-3 gap-3 text-sm">
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
            <div class="text-gray-500 text-xs">Montant / échéance</div>
            <div class="font-bold">{{ number_format($partieAdverse->montant_echeance, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
            <div class="text-gray-500 text-xs">Périodicité</div>
            <div class="font-bold">{{ $partieAdverse->periodicite_label }}</div>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
            <div class="text-gray-500 text-xs">Reste à payer</div>
            <div class="font-bold {{ $partieAdverse->reste_a_payer > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($partieAdverse->reste_a_payer, 0, ',', ' ') }} FCFA
            </div>
        </div>
    </div>

    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-800">
                <th class="p-2 text-left border">N°</th>
                <th class="p-2 text-left border">Date</th>
                <th class="p-2 text-right border">Montant dû</th>
                <th class="p-2 text-right border">Versé (cumulé)</th>
                <th class="p-2 text-right border">Reste dû</th>
                <th class="p-2 text-left border">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partieAdverse->echeancier as $echeance)
                <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-900' : '' }}">
                    <td class="p-2 border">{{ $echeance['numero'] }}</td>
                    <td class="p-2 border">{{ $echeance['date_echeance']->format('d/m/Y') }}</td>
                    <td class="p-2 border text-right">{{ number_format($echeance['montant_echeance'], 0, ',', ' ') }}</td>
                    <td class="p-2 border text-right text-green-700">{{ number_format($echeance['montant_verse_cumule'], 0, ',', ' ') }}</td>
                    <td class="p-2 border text-right {{ $echeance['reste_periode'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($echeance['reste_periode'], 0, ',', ' ') }}
                    </td>
                    <td class="p-2 border">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @switch($echeance['statut'])
                                @case('payee') bg-green-100 text-green-800 @break
                                @case('partielle') bg-orange-100 text-orange-800 @break
                                @case('en_retard') bg-red-100 text-red-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ $echeance['statut_label'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">Aucune échéance générée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
