<div class="fi-modal-content space-y-4">
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
        <div class="text-gray-500 text-xs">Solde disponible à répartir</div>
        <div class="font-bold text-lg {{ $resultat['solde_disponible'] < 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ number_format($resultat['solde_disponible'], 0, ',', ' ') }} FCFA
        </div>
    </div>

    @if($resultat['avertissement'])
        <div class="p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-300 text-orange-800 dark:text-orange-300 text-sm">
            {{ $resultat['avertissement'] }}
        </div>
    @endif

    @if($resultat['repartitions']->isNotEmpty())
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="p-2 text-left border">Ayant droit</th>
                    <th class="p-2 text-left border">Rôle</th>
                    <th class="p-2 text-right border">Part (%)</th>
                    <th class="p-2 text-right border">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultat['repartitions'] as $ligne)
                    <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-900' : '' }}">
                        <td class="p-2 border font-medium">{{ $ligne['ayant_droit']->nom_complet }}</td>
                        <td class="p-2 border">{{ $ligne['role_label'] }}</td>
                        <td class="p-2 border text-right">{{ $ligne['part_pourcentage'] }}%</td>
                        <td class="p-2 border text-right font-bold text-green-700">
                            {{ number_format($ligne['montant'], 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="p-4 text-center text-gray-500">
            Aucune répartition calculable pour le moment.
        </div>
    @endif
</div>
